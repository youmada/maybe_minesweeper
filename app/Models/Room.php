<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_participants',
        'is_private',
        'is_game_started',
        'is_game_clear',
        'is_game_over',
        'game_id',
    ];

    protected $guarded =
        [
            'magic_link_token',
        ];

    protected $hidden = [
        'magic_link_token',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
