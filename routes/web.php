<?php

use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\MultiRoomController;
use App\Http\Controllers\MultiRoomJoinController;
use App\Http\Controllers\PlayContinueController;
use App\Http\Controllers\PlayerHeartBeatController;
use App\Http\Controllers\ResponseGameStatesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ホーム画面ルート
Route::get('/', function () {
    return Inertia::render('Home');
})->name('Home');

// シングルプレイ設定画面ルート
Route::get('/single', function () {
    return Inertia::render('Single');
});

// シングルプレイ
Route::get('/single/play', function () {
    return Inertia::render('Single/Play', [
        'level' => request()->query('level'), // levelにマインスイーパーの難易度を入れる
    ]);
});

// マルチプレイ

// マルチルーム
Route::resource('multi/rooms', MultiRoomController::class)->only(['index', 'create', 'store', 'destroy']);

// マルチルーム参加
Route::get('multi/rooms/{room}/join', [MultiRoomJoinController::class, '__invoke'])
    ->whereUuid('room')
    ->name('multi.rooms.join');

// マルチゲームプレイ
Route::group(['middleware' => ['room.auth', 'auth:magicLink']], function () {
    // ゲーム画面表示
    Route::get('multi/rooms/{room}/play', [GamePlayController::class, 'show'])
        ->whereUuid('room')
        ->name('multi.rooms.play.show');
    // ゲーム開始 - プレイヤー待機画面から、初回クリックのゲーム開始フェーズへの移行
    Route::post('multi/rooms/{room}/play/start', [GamePlayController::class, 'store'])
        ->whereUuid('room')
        ->name('multi.rooms.play.store');
    // ゲーム場面操作
    Route::put('multi/rooms/{room}/play/operate', [GamePlayController::class, 'update'])
        ->whereUuid('room')
        ->name('multi.rooms.play.update');
    // ゲームデータ削除 ゲームをやり直すなどの機能を作るときに、実装する。
    //    Route::delete('multi/rooms/{room}/play', [GamePlayController::class, 'destroy'])
    //        ->whereUuid('room')
    //        ->name('multi.rooms.play.destroy');

    // ゲームプレイコンティニュー
    Route::post('multi/rooms/{room}/play/continue', [PlayContinueController::class, '__invoke'])
        ->whereUuid('room')
        ->name('multi.rooms.play.continue');

    // プレイヤールーム在籍チェック
    Route::put('multi/rooms/{room}/play/heartbeat', [PlayerHeartBeatController::class, '__invoke'])
        ->whereUuid('room')
        ->name('multi.rooms.play.heartbeat');

    // webSocketを使わずに、ボードデータを送信するためのコントローラ
    Route::get('multi/rooms/{room}/play/reflection', [ResponseGameStatesController::class, '__invoke'])
        ->whereUuid('room')
        ->name('multi.rooms.play.reflection');

});
// require __DIR__.'/auth.php';
