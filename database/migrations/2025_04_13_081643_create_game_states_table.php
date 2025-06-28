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
        Schema::create('game_states', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Room::class)
                ->constrained()
                ->onDelete('cascade');
            $table->integer('width');
            $table->integer('height');
            $table->integer('num_of_mines')->comment('地雷数');
            $table->json('tile_states')->comment('タイルの配置情報や地雷の有無など');
            $table->boolean('is_game_started')->default(false);
            $table->boolean('is_game_clear')->default(false);
            $table->boolean('is_game_over')->default(false);
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
