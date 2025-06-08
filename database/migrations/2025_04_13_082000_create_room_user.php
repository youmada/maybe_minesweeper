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
        Schema::create('room_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Room::class)->constrained()->onDelete('cascade');
            $table->string('user_id')->comment('参加者ID(セッションを予定）');
            $table->timestamp('joined_at')->useCurrent(); // useCurrentはデフォルト値を作成時間にする
            $table->timestamp('left_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_users');
    }
};
