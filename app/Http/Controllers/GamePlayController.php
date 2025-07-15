<?php

namespace App\Http\Controllers;

use App\Domain\Minesweeper\TileActionMode;
use App\Events\FetchRoomData;
use App\Http\Resources\MultiPlayGameResource;
use App\Models\GameState;
use App\Models\Room;
use App\Models\RoomState;
use App\Services\Minesweeper\MinesweeperService;
use App\Services\Multi\AdvanceTurnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GamePlayController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Room $room)
    {
        return Inertia::render('Multi/Play', ['data' => MultiPlayGameResource::make($room)->resolve()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room, AdvanceTurnService $advanceTurnService, MinesweeperService $minesweeperService)
    {

        $roomState = RoomState::where('room_id', $room->id)->first();
        $gameState = GameState::where('room_id', $room->id)->first();
        $width = $gameState->width - 1;
        $height = $gameState->height - 1;
        $this->authorize('update', $roomState);

        $attributes = $request->validate([
            'x' => ['required', 'integer', 'min:0', "max:{$width}"],
            'y' => ['required', 'integer', 'min:0', "max:{$height}"],
            'operation' => ['required', 'string', 'in:open,flag'],
        ]);

        if ($roomState->status === 'standby') {
            if ($attributes['operation'] === 'flag') {
                return response()->json(['status' => 'invalid operation', 'message' => '予期しない処理を検知しました'], 400);
            }
            try {
                DB::transaction(function () use ($room, $roomState, $minesweeperService, $attributes) {
                    $minesweeperService->processGameStart($room->id, $attributes['x'], $attributes['y']);

                    $roomState->update(['status' => 'playing']);
                });
            } catch (\Throwable $e) {
                Log::error($e->getMessage());

                return response()->json(['status' => 'error', 'message' => '更新処理でエラーが発生しました'], 500);
            }

            return response()->json(['status' => 'gameStart'], 201);
        }

        $tileOperation = $attributes['operation'] === 'open' ? TileActionMode::OPEN : TileActionMode::FLAG;

        try {
            DB::transaction(function () use ($request, $room, $advanceTurnService, $minesweeperService, $attributes, $tileOperation) {
                // advanceTurnServiceに渡すプレイヤーIDはpublic_idの必要あり。内部で
                $advanceTurnService($room->id, $request->user()->public_id, $tileOperation);
                $minesweeperService->handleClickTile($room->id, $attributes['x'], $attributes['y'], $tileOperation);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => 'error', 'message' => '内部でエラーが発生しました。'], 500);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => 'error', 'message' => '更新処理でエラーが発生しました'], 500);
        }

        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        //
    }

    // ゲーム開始処理を行う
    public function store(Request $request, Room $room)
    {
        $roomState = RoomState::where('room_id', $room->id)->first();
        $roomState->update(['status' => 'standby']);

        FetchRoomData::dispatch($room);

        return response()->json(['status' => 'ok'], 201);
    }
}
