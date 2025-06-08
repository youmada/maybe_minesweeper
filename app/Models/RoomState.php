<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomState extends Model
{
    protected $fillable = [
        'room_id',
        'room_user_id',
        'game_state_id',
        'turn_order',
        'status',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function roomUser(): BelongsTo
    {
        return $this->belongsTo(RoomUser::class);
    }

    public function gameState(): BelongsTo
    {
        return $this->belongsTo(GameState::class);
    }

    protected function casts(): array
    {
        return [
            'turn_order' => 'array',
        ];
    }
}
