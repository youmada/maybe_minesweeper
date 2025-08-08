<?php

use App\Models\Player;
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->binary('public_id', 16)->unique();
            $table->string('name');
            $table->foreignIdFor(Player::class, 'owner_id')->constrained()->onDelete('cascade');
            $table->string('magic_link_token')->unique()->index();
            $table->integer('max_player')->comment('ルーム最大参加人数');
            $table->boolean('is_private')->default(true);
            $table->timestamp('waiting_at')->nullable()->index()->comment('最後のプレイヤーがルームから退出した時のタイムスタンプ');
            $table->timestamp('backup_at')->nullable()->index()->comment('ルームとゲームデータをredisからDBに退避時のタイムスタンプ');
            $table->date('expire_at')->default(now()->addWeek())->comment('ルーム有効期限。デフォルトでは1週間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
