<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameState extends Model
{
    protected $fillable = [
        'width',
        'height',
        'num_of_mines',
        'tile_states',
        'game_id',
        'is_game_started',
        'is_game_clear',
        'is_game_over',
    ];

    protected $table = 'game_states';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
