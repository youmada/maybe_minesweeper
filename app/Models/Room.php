<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'game_id',
        'max_player',
        'magic_link_token',
        'players',
        'is_private',
        'expire_at',
    ];

    protected $hidden = [
        'magic_link_token',
    ];

    protected $casts = [
        'id' => 'string',
        'players' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'expire_at' => 'datetime:Y-m-d H:i:s',
        'is_private' => 'boolean',
    ];

    public function roomStates(): HasMany
    {
        return $this->hasMany(RoomState::class);
    }

    public function isExpired(): bool
    {
        return $this->expire_at > now();
    }

    public function isRoomJoined(string $playerId): bool
    {
        /**
         * ルームへの参加可否チェック
         *
         * - すでに参加済みのプレイヤーは常に true
         * - 未参加のプレイヤーは max_player 未満なら true
         */
        if (in_array($playerId, $this->players, true)) {
            return true;
        }

        return count($this->players) < $this->max_player;
    }

    public function searchByMagicLinkToken(string $magicLinkToken): ?Room
    {
        return Room::where('magic_link_token', $magicLinkToken)->first();
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // スネークケースからキャメルケースにキーを変換
        $camelCasedArray = [];
        foreach ($array as $key => $value) {
            $camelCasedArray[Str::camel($key)] = $value;
        }

        return $camelCasedArray;
    }

    public function toArrayWithMagicLink(): array
    {
        $array = $this->toArray();

        $array['magicLinkToken'] = $this->magic_link_token;

        return $array;
    }
}
