<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Repositories\DB\MinesweeperRepository as DbGameRepo;
use App\Repositories\DB\RoomRepository as DbRoomRepo;
use App\Repositories\Redis\MinesweeperRepository as RedisGameRepo;
use App\Repositories\Redis\RoomRepository as RedisRoomRepo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackUpDataFromRedisToDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:data-from-redis-to-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run back up data from redis to db when player is not exsist the room';

    public function __construct(
        protected RedisRoomRepo $redisRoomRepo,
        protected DbRoomRepo $dbRoomRepo,
        protected RedisGameRepo $redisGameRepo,
        protected DbGameRepo $dbGameRepo
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseTime = now()->subMinutes(5);

        // ルームを見る waiting_atが存在している場合、プレイヤーがいないことと同義になる。
        $rooms = Room::where('waiting_at', '<=', $baseTime)
            ->whereNull('backup_at')->get();

        foreach ($rooms as $room) {
            // ルームのデータ(ゲームデータ・ルームデータ）をredisにあるか、確認する
            if (empty($this->redisRoomRepo->get($room->id)) && empty($this->redisGameRepo->getState($room->id))) {
                logger()->warning('バックアップコマンド：redisデータがないです。ルームID：'.$room->id);

                continue;
            }

            $roomData = $this->redisRoomRepo->get($room->id);
            $gameData = $this->redisGameRepo->getState($room->id);

            try {
                DB::transaction(function () use ($room, $roomData, $gameData) {
                    // redis から DBへの退避処理
                    $this->dbRoomRepo->update($roomData, $room->id);
                    $this->dbGameRepo->updateState($gameData, $room->id);
                    $room->update(['backup_at' => Carbon::now()->toDateTimeString()]);

                    // redisデータ削除処理
                    $this->redisRoomRepo->delete($room->id);
                    $this->redisGameRepo->deleteState($room->id);
                });
            } catch (\Throwable $e) {
                logger()->error('バックアップコマンドでエラー発生'.$e);
            }
        }
    }
}
