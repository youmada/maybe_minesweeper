<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUser extends Model
{
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    protected function casts(): array
    {
        return [
            'joined_at' => 'timestamp',
        ];
    }
}
