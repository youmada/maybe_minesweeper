<?php

namespace App\Http\Controllers;

use App\Events\PlayerJoinedRoom;
use App\Http\Resources\MultiPlayGameResource;
use App\Models\Room;
use App\Models\RoomState;
use App\Repositories\Composites\GameCompositeRepository;
use App\Services\Minesweeper\MinesweeperService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GamePlayController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Room $room)
    {
        $playerId = $request->session()->get('player_id') ?? '';
        PlayerJoinedRoom::dispatch($room->id, $playerId);

        return Inertia::render('Multi/Play', ['data' => MultiPlayGameResource::make($room)->resolve()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        //
    }

    // ゲーム開始処理を行う
    public function store(Request $request, Room $room, GameCompositeRepository $gameCompositeRepository)
    {
        $gameService = app(MinesweeperService::class);
        $gameState = $gameCompositeRepository->getState($room->id);
        $roomState = RoomState::where('room_id', $room->id)->first();

        return response()->json([
            'game' => [
                'board' => $gameService->getGameStateForClient($gameState),
                'width' => $gameState->getWidth(),
                'height' => $gameState->getHeight(),
            ],

            'room' => [
                'turnOrder' => $roomState->turn_order,
                'status' => $roomState->status,
            ],
        ]);
    }
}
