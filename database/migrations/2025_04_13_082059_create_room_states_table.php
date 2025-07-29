<?php

use App\Models\Room;
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
        Schema::create('room_states', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Room::class)
                ->constrained()
                ->onDelete('cascade');
            $table->json('turn_order')->nullable()->comment('ターン順番');
            $table->string('current_player')->nullable()->comment('現在ターンのプレイヤーID');
            $table->integer('flag_limit')->default(0)->comment('フラグ操作上限');
            $table->enum('status', ['waiting', 'standby', 'playing', 'finished'])
                ->default('waiting')
                ->index()->comment('waiting: ルーム作成かつゲームデータ作成完了, standby: タイル生成完了初回クリック前, playing: ゲームプレイ中, finished: ゲーム終了');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_states');
    }
};
