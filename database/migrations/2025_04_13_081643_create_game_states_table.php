<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_states', function (Blueprint $table) {
            $table->id();
            $table->integer('width');
            $table->integer('height');
            $table->integer('num_of_mines')->comment('地雷数。');
            $table->json('tile_states')->comment('タイルの配置情報や地雷の有無など');
            $table->uuid('game_id')->unique()->comment('サービス層で設定するuuidがここに入る。redisとDBで共通で使う。');
            $table->boolean('is_game_started');
            $table->boolean('is_game_clear');
            $table->boolean('is_game_over');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_states');
    }
};
