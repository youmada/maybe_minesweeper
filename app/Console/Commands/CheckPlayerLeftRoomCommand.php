<?php

namespace App\Console\Commands;

use App\Events\RoomPlayerList;
use App\Models\Player;
use App\Models\Room;
use App\Repositories\Composites\RoomCompositeRepository;
use DB;
use Illuminate\Console\Command;

class CheckPlayerLeftRoomCommand extends Command
{
    protected $signature = 'check:player-left-room';

    protected $description = 'Check if the player has left the room for more than 30 seconds';

    public function handle(RoomCompositeRepository $roomCompositeRepository): void
    {
        // ルームに入っているプレイヤー一覧を探索する room_playerを確認する
        $baseTime = now()->subSeconds(30);

        // ルーム在籍確認の最新時間から30秒経っていると、強制退席の対象になる。

        $relevantRecord = DB::table('room_player')
            ->whereNull('left_at')
            ->where('last_exists_at', '<=', $baseTime)
            ->get();

        foreach ($relevantRecord as $record) {
            DB::table('room_player')
                ->where('room_id', $record->room_id)
                ->where('player_id', $record->player_id)
                ->update(['left_at' => now()]);
            // 該当プレイヤーがいるルームを取得
            $currentRoom = $roomCompositeRepository->get($record->room_id);
            $relevantPlayer = Player::find($record->player_id);

            // プレイヤー強制退出処理
            $currentRoom->leave($relevantPlayer->public_id);
            Player::find($record->player_id)->rooms()->detach($record->room_id);

            $roomCompositeRepository->update($currentRoom, $record->room_id);
            // ターンステートがリセットされる可能性もあるので、イベントを発火させる
            RoomPlayerList::dispatch(Room::find($record->room_id));
        }
    }
}
