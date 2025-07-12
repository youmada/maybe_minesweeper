<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Player extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'public_id',
    ];

    // プレイヤー識別子を取得（将来的にAuthに切り替えやすいように）
    public static function getPlayerIdentifier(): ?string
    {
        return session('public_id', null); // 今はセッション、将来は Auth::id() に変更可
    }

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
