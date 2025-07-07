<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Player extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'session_id',
    ];

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_player')
            ->withPivot('joined_at', 'left_at')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'joined_at' => 'timestamp',
        ];
    }
}
