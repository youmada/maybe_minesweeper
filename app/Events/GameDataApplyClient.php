<?php

namespace App\Events;

use App\Domain\Minesweeper\GameState;
use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;
use App\Services\Minesweeper\MinesweeperService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameDataApplyClient implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(protected Room $room, protected GameState $previousGameState) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("game.{$this->room->public_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        $gameState = app(GameCompositeRepository::class)->getState($this->room->id);

        return [
            'data' => app(MinesweeperService::class)->getGameStateForClient($gameState, $this->previousGameState),
        ];
    }
}
