<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'joined_at' => 'timestamp',
        ];
    }
}
