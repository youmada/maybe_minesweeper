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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->binary('public_id', 16)->unique();
            $table->string('name');
            $table->string('owner_id');
            $table->string('magic_link_token')->unique()->index();
            $table->integer('max_player')->comment('ルーム最大参加人数');
            $table->json('players');
            $table->boolean('is_private')->default(true);
            $table->timestamp('last_activity_at')->nullable()->index();
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
