<?php

namespace App\Repositories\DB;

use App\Domain\Minesweeper\GameState as State;
use App\Exceptions\RepositoryException;
use App\Models\GameState;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class MinesweeperRepository implements GameRepositoryInterface
{
    /**
     * @throws RepositoryException
     */
    public function saveState(State $state, $roomId): void
    {
        if (GameState::where('room_id', $roomId)->whereNot('is_game_over', true)->exists()) {
            return;
        }
        try {
            $toArrayState = $state->toArray();

            $mappedState = [
                'width' => $toArrayState['width'],
                'height' => $toArrayState['height'],
                'num_of_mines' => $toArrayState['numOfMines'],
                'tile_states' => $toArrayState['tileStates'],
                'is_game_started' => $toArrayState['isGameStarted'],
                'is_game_clear' => $toArrayState['isGameClear'],
                'is_game_over' => $toArrayState['isGameOver'],
            ];

            GameState::create(
                $mappedState + ['room_id' => $roomId]
            );
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'saveState');
        }
    }

    public function getState(string $roomId): ?State
    {
        if (GameState::where('room_id', $roomId)->exists()) {
            $stateModel = GameState::where('room_id', $roomId)->first();

            return State::fromPrimitive(
                $stateModel->tile_states,
                $stateModel->width,
                $stateModel->height,
                $stateModel->num_of_mines,
                $stateModel->is_game_started,
                $stateModel->is_game_clear,
                $stateModel->is_game_over,
            );
        }

        return null;
    }

    /**
     * @throws RepositoryException
     */
    public function updateState(State $state, string $roomId): void
    {
        try {
            $stateModel = GameState::where('room_id', $roomId)->firstOrFail();
            $toArrayState = $state->toArray();
            $mappedState = [
                'width' => $toArrayState['width'],
                'height' => $toArrayState['height'],
                'num_of_mines' => $toArrayState['numOfMines'],
                'tile_states' => $toArrayState['tileStates'],
                'is_game_started' => $toArrayState['isGameStarted'],
                'is_game_clear' => $toArrayState['isGameClear'],
                'is_game_over' => $toArrayState['isGameOver'],
            ];
            $stateModel->update(
                $mappedState + ['room_id' => $roomId]
            );
        } catch (ModelNotFoundException $exception) {
            throw new RepositoryException("GameState not found for room_id={$roomId}", 0, $exception);
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'updateState');
        }

    }

    /**
     * @throws RepositoryException
     */
    public function deleteState(string $roomId): void
    {
        try {
            $target = GameState::where('room_id', $roomId)->firstOrFail();
            $target->delete();
        } catch (ModelNotFoundException $exception) {
            throw new RepositoryException("GameState not found for room_id={$roomId}", 0, $exception);
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'deleteState');
        }
    }
}
