<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameState extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'width',
        'height',
        'num_of_mines',
        'tile_states',
        'is_game_started',
        'is_game_clear',
        'is_game_over',
    ];

    public function rooms(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getNumOfMinesAttribute()
    {
        return $this->attributes['num_of_mines'];
    }

    public function setNumOfMinesAttribute($value): void
    {
        $this->attributes['num_of_mines'] = $value;
    }

    public function getTileStatesAttribute()
    {
        return json_decode($this->attributes['tile_states'], true);
    }

    public function setTileStatesAttribute($value): void
    {
        $this->attributes['tile_states'] = json_encode($value);
    }

    public function getIsGameStartedAttribute(): bool
    {
        return (bool) $this->attributes['is_game_started'];
    }

    public function setIsGameStartedAttribute($value): void
    {
        $this->attributes['is_game_started'] = $value;
    }

    public function getIsGameClearAttribute(): bool
    {
        return (bool) $this->attributes['is_game_clear'];
    }

    public function setIsGameClearAttribute($value): void
    {
        $this->attributes['is_game_clear'] = $value;
    }

    public function getIsGameOverAttribute(): bool
    {
        return (bool) $this->attributes['is_game_over'];
    }

    public function setIsGameOverAttribute($value): void
    {
        $this->attributes['is_game_over'] = $value;
    }

    protected $table = 'game_states';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'tile_states' => 'array',
    ];

    public function toArray(): array
    {
        // デフォルトの返却値をさらにカスタマイズ可能
        return parent::toArray();
    }
}
