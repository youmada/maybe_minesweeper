<?php

namespace App\Http\Controllers;

use App\Domain\Minesweeper\TileActionMode;
use App\Domain\Room\RoomStatus;
use App\Events\GameDataApplyClient;
use App\Events\GameStatesReflectionSignalEvent;
use App\Events\RoomStateApplyClientEvent;
use App\Events\RoomStatusApplyClient;
use App\Http\Resources\MultiPlayGameResource;
use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\Composites\RoomCompositeRepository;
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

        // 元データ取得
        $roomState = app(RoomCompositeRepository::class)->get($room->id);
        $gameState = app(GameCompositeRepository::class)->getState($room->id);
        $width = $gameState->getWidth() - 1;
        $height = $gameState->getHeight() - 1;

        $this->authorize('update', $roomState);

        $attributes = $request->validate([
            'x' => ['required', 'integer', 'min:0', "max:{$width}"],
            'y' => ['required', 'integer', 'min:0', "max:{$height}"],
            'operation' => ['required', 'string', 'in:open,flag'],
        ]);

        // 初回クリック処理
        if ($roomState->getRoomStatus() === 'standby') {
            if ($attributes['operation'] === 'flag') {
                return response()->json(['message' => '予期しない処理を検知しました'], 400);
            }

            try {
                DB::transaction(function () use ($room, $roomState, $minesweeperService, $attributes) {
                    $minesweeperService->processGameStart($room->id, $attributes['x'], $attributes['y']);

                    $roomState->startRoom();
                    app(RoomCompositeRepository::class)->update($roomState, $room->id);
                });
            } catch (\Throwable $e) {
                Log::error($e->getMessage());

                return response()->json(['message' => '更新処理でエラーが発生しました'], 500);
            }
            // ターン更新イベント
            RoomStateApplyClientEvent::dispatch($room);

            // ルームとゲームのステータス通知イベント
            RoomStatusApplyClient::dispatch($room);
            // 他プレイヤーに通知するためのイベント
            GameStatesReflectionSignalEvent::dispatch($room);

            // タイル数が多いとwebSocketではエラーになるので、例外的に最初のクリックだけhttpsで送信
            return response()->json(['data' => app(MinesweeperService::class)->getGameStateForClient(app(GameCompositeRepository::class)->getState($room->id)),
            ], 201);
        }

        $tileOperation = $attributes['operation'] === 'open' ? TileActionMode::OPEN : TileActionMode::FLAG;

        try {
            DB::transaction(function () use ($request, $room, $advanceTurnService, $minesweeperService, $attributes, $tileOperation, $roomState) {
                // advanceTurnServiceに渡すプレイヤーIDはpublic_idの必要あり。内部で
                $advanceTurnService($room->id, $request->user()->public_id, $tileOperation);
                $gameState = $minesweeperService->handleClickTile($room->id, $attributes['x'], $attributes['y'], $tileOperation);

                // ゲームがクリアかゲームオーバーしたかチェック
                if ($gameState->isGameEnded()) {
                    $roomState->endRoom();
                    app(RoomCompositeRepository::class)->update($roomState, $room->id);
                }

            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['message' => '内部でエラーが発生しました。'], 500);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return response()->json(['message' => '更新処理でエラーが発生しました'], 500);
        }
        // 盤面更新とターン更新イベント
        GameDataApplyClient::dispatch($room, $gameState);
        RoomStateApplyClientEvent::dispatch($room);

        // ルームとゲームのステータス通知イベント
        RoomStatusApplyClient::dispatch($room);

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
    public function store(Request $request, Room $room, RoomCompositeRepository $roomCompositeRepository)
    {
        $roomAggregate = $roomCompositeRepository->get($room->id);
        $this->authorize('restore', $roomAggregate);

        $roomState = $roomCompositeRepository->get($room->id);
        $roomState->getRoomState()->changeStatus(RoomStatus::STANDBY);

        try {
            $roomCompositeRepository->update($roomState, $room->id);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => 'error'], 500);
        }

        RoomStatusApplyClient::dispatch($room);

        return response()->json(['status' => 'ok'], 201);
    }
}
