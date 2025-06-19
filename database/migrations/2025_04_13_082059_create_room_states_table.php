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
            $table->integer('flag_limit')->default(0)->comment('フラグ操作上限');
            $table->enum('status', ['waiting', 'playing', 'finished'])
                ->default('waiting')
                ->index();
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
