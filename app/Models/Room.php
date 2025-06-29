<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected static function booted(): void
    {
        // 作成時にpublic_idカラムにバイナリ形式のUUIDをセット
        static::creating(function ($room) {
            $room->public_id = UUid::uuid4()->getBytes();
        });
    }

    protected $fillable = [
        'public_id',
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
        'is_private' => 'boolean',
    ];

    public function roomStates(): HasMany
    {
        return $this->hasMany(RoomState::class);
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

    public function getMagicLinkUrlAttribute(): string
    {
        return config('app.url')."/multi/rooms/join/{$this->public_id}?token={$this->magic_link_token}";
    }
}
