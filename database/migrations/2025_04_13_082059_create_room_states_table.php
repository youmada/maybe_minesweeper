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
        Schema::create('room_states', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor('rooms')->constrained();
            $table->foreignIdFor('boards')->constrained();
            $table->json('turn_info');
            $table->string('game_status')->comment('待機中・進行中・終了などのゲーム進行ステータス');
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
