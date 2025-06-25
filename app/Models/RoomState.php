<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RoomState extends Model
{
    protected $fillable = [
        'room_id',
        'flag_limit',
        'turn_order',
        'status',
    ];

    public function rooms(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    protected function casts(): array
    {
        return [
            'turn_order' => 'array',
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
