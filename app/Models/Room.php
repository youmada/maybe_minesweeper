<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Room extends Model
{
    use HasFactory;

    public $incrementing = true;

    protected $keyType = 'int';

    public function getRouteKeyName(): string
    {
        // ルートでのバインディングをidではなくpublic_idで行うようにする
        return 'public_id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        // 入力された文字列UUIDをバイナリ形式に変換してWHERE検索
        return static::where('public_id', Uuid::fromString($value)->getBytes())->firstOrFail();
    }

    protected $fillable = [
        'public_id',
        'name',
        'owner_id',
        'max_player',
        'magic_link_token',
        'is_private',
        'expire_at',
    ];

    protected $hidden = [
        'magic_link_token',
    ];

    protected $casts = [
        'id' => 'string',
        'owner_id' => 'string',
        'is_private' => 'boolean',
    ];

    public function roomStates(): BelongsTo
    {
        return $this->belongsTo(RoomState::class);
    }

    public function players(): belongsToMany
    {
        return $this->belongsToMany(Player::class, 'room_player')
            ->withPivot('joined_at', 'left_at')
            ->withTimestamps();
    }

    public function setPublicIdAttribute($value): void
    {
        $this->attributes['public_id'] = Uuid::fromString($value)->getBytes();
    }

    public function getPublicIdAttribute($value): string
    {
        // バイナリ形式のUUIDを文字列として取得するアクセサ
        return Uuid::fromBytes($value)->toString();
    }

    public function isExpired(): bool
    {
        return $this->expire_at > Carbon::today();
    }

    public function isFull(): bool
    {
        return $this->players()->count() >= $this->max_player;
    }

    public function isRoomJoined(string $playerId): bool
    {
        /**
         * ルームへの参加可否チェック
         *
         * - すでに参加済みのプレイヤーは常に true
         * - 未参加のプレイヤーは max_player 未満なら true
         */
        if ($this->players()->where('public_id', $playerId)->exists()) {
            return true;
        }

        return $this->players()->count() < $this->max_player;
    }

    public function searchByMagicLinkToken(string $magicLinkToken): ?Room
    {
        return Room::where('magic_link_token', $magicLinkToken)->first();
    }

    public function scopeFindByPublicId($query, string $publicId)
    {
        return $query->where('public_id', Uuid::fromString($publicId)->getBytes());
    }

    public static function canJoin(string $roomPublicId, ?string $sessionId): bool
    {
        $room = Room::where('expire_at', '>', Carbon::now())
            ->findByPublicId($roomPublicId)
            ->first();
        if (! $room) {
            return false;
        }

        if ($sessionId === null) {
            return false;
        }

        return $room->players()->where('public_id', $sessionId)->exists();
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // スネークケースからキャメルケースにキーを変換
        $camelCasedArray = [];
        foreach ($array as $key => $value) {
            $camelCasedArray[Str::camel($key)] = $value;
        }

        $camelCasedArray['players'] = $this->players()->get()->map(function ($player) {
            return $player->public_id;
        })->toArray();

        $camelCasedArray['ownerId'] = $this->players()->first()?->public_id;

        return $camelCasedArray;
    }

    public function toArrayWithMagicLink(): array
    {
        $array = $this->toArray();

        $array['magicLinkToken'] = $this->magic_link_token;

        return $array;
    }

    public function getMagicLinkUrlAttribute(): string
    {
        return config('app.url')."/multi/rooms/{$this->public_id}/join?token={$this->magic_link_token}";
    }
}
