<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;
use App\Services\Minesweeper\MinesweeperService;

class ResponseGameStatesController extends Controller
{
    public function __invoke(Room $room)
    {
        return response()->json(['data' => app(MinesweeperService::class)->getGameStateForClient(app(GameCompositeRepository::class)->getState($room->id)),
        ], 201);
    }
}
