<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RoomState extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'flag_limit',
        'turn_order',
        'current_player',
        'status',
    ];

    public function rooms(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getCurrentPlayerAttribute()
    {
        return Player::find($this->attributes['current_player'])->session_id ?? null;
    }

    protected function casts(): array
    {
        return [
            'turn_order' => 'array',
            'current_player' => 'string',
        ];
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
}
