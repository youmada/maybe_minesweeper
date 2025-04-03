<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
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
        'level' => request()->query('level'),// levelにマインスイーパーの難易度を入れる
    ]);
});

// マルチプレイ設定画面ルート
Route::get('/multi', function () {
    return Inertia::render('Multi');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
