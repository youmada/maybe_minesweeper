<?php

use App\Http\Controllers\MultiRoomController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ホーム画面ルート
Route::get('/', function () {
    return Inertia::render('Home');
});

// シングルプレイ設定画面ルート
Route::get('/single', function () {
    return Inertia::render('Single');
});

Route::get('/single/play', function () {
    return Inertia::render('Single/Play', [
        'level' => request()->query('level'), // levelにマインスイーパーの難易度を入れる
    ]);
});

// マルチプレイ

Route::resource('multi/rooms', MultiRoomController::class)->only(['index', 'create', 'store', 'destroy']);

//Route::get('/multi', function () {
//    return Inertia::render('Multi');
//});
//Route::post('/multi/create', [MultiplayerController::class, 'createRoom'])->name('multiplayer.create');

//require __DIR__.'/auth.php';
