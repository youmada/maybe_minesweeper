<?php

use App\Domain\Room\RoomStatus;
use App\Models\Player;
use App\Models\Room;
use App\Repositories\DB\MinesweeperRepository as DBGameRepo;
use App\Repositories\DB\RoomRepository as DBRoomRepo;
use App\Repositories\Redis\MinesweeperRepository as RedisGameRepo;
use App\Repositories\Redis\RoomRepository as RedisRoomRepo;
use App\Services\Minesweeper\MinesweeperService;
use App\Services\Multi\CreateRoomService;
use App\Services\Multi\JoinRoomService;

beforeEach(function () {
    $this->freezeTime();

    $this->player = Player::factory()->create();
    // ルーム作成
    $this->roomID = app(CreateRoomService::class)(
        'room name',
        3,
        $this->player->public_id,
        now()->addDay(),
        true,
        [],
        5);
    $this->room = Room::find($this->roomID);
    app(JoinRoomService::class)($this->room->id, $this->player->public_id);

    // ゲームを開始する
    app(MinesweeperService::class)->initializeGame($this->room->id, 5, 5, 5);

    DB::table('room_player')->where('player_id', $this->player->id)->update(['last_exists_at' => now()]);
});

it('should back up the room and game data from redis to DB when no players is the room. In the case of game data', function () {
    // 事前チェック：redisにデータがあるか
    $redisGameData = app(RedisGameRepo::class)->getState($this->roomID);
    expect($redisGameData)->not()->toBeNull();

    // プレイヤーをルームから退出させる
    $this->room->players()->detach($this->player->id);
    $this->room->update(['waiting_at' => now()->subMinutes(5)]);
    $this->room->refresh();

    // データを更新する
    $redisGameData->endGame(isWin: true);
    app(RedisGameRepo::class)->updateState($redisGameData, $this->roomID);

    // 実行
    $this->artisan('backup:data-from-redis-to-db');
    // アサート
    // おそらくDBのデータをprevとして取得して、それをコマンド実行後の内容と比べる必要がある。
    expect(app(DBGameRepo::class)->getState($this->roomID)->toArray())->toEqual($redisGameData->toArray());
});

it('should back up the room and game data from redis to DB when no players is the room. In the case of room data', function () {
    // 事前チェック：redisにデータがあるか
    $redisRoomData = app(RedisRoomRepo::class)->get($this->roomID);
    expect($redisRoomData)->not()->toBeNull();

    // プレイヤーをルームから退出させる
    $this->room->players()->detach($this->player->id);
    $this->room->update(['waiting_at' => now()->subMinutes(5)]);
    $this->room->refresh();

    // データを更新する
    $redisRoomData->changeStatus(RoomStatus::FINISHED);

    app(RedisRoomRepo::class)->update($redisRoomData, $this->roomID);
    // 実行
    $this->artisan('backup:data-from-redis-to-db');
    // アサート
    // おそらくDBのデータをprevとして取得して、それをコマンド実行後の内容と比べる必要がある。
    expect(app(DBRoomRepo::class)->get($this->roomID)->getRoomStatus())->toEqual($redisRoomData->getRoomStatus());
});
