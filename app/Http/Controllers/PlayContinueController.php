<?php

namespace App\Http\Controllers;

use App\Domain\Room\RoomStatus;
use App\Events\GameDataApplyClient;
use App\Events\RoomStateApplyClientEvent;
use App\Events\RoomStatusApplyClient;
use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\Composites\RoomCompositeRepository;
use App\Services\Minesweeper\MinesweeperService;
use Exception;
use Illuminate\Http\Request;

class PlayContinueController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request, Room $room, MinesweeperService $minesweeperService)
    {
        $roomRepository = app(RoomCompositeRepository::class);
        $gameRepository = app(GameCompositeRepository::class);
        // 元データ取得
        $roomState = $roomRepository->get($room->id) ?? throw new Exception("Game not found: {$room->id}");

        $this->authorize('continue-action', $roomState);

        // ゲームデータを削除&ゲームデータを再構築
        $newGameState = $minesweeperService->continueGame($room->id);

        // ルーム状態を変更する finished->playing
        $roomState->changeStatus(RoomStatus::STANDBY);

        // 状態を保存する
        $roomRepository->update($roomState, $room->id);
        $gameRepository->saveState($newGameState, $room->id);

        RoomStateApplyClientEvent::dispatch($room);
        RoomStatusApplyClient::dispatch($room);
        GameDataApplyClient::dispatch($room);

        return response()->json(['message' => 'ok'], 201);
    }
}
