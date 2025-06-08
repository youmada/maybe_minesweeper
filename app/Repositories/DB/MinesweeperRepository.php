<?php

namespace App\Repositories\DB;

use App\Domain\Minesweeper\GameState as State;
use App\Exceptions\RepositoryException;
use App\Models\GameState;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class MinesweeperRepository implements GameRepositoryInterface
{
    /**
     * @throws Exception
     */
    public function saveState(State $state, string $gameId): void
    {
        if (GameState::where('game_id', $gameId)->exists()) {
            return;
        }
        try {
            $toArrayState = $state->toArray();
            GameState::create(
                [
                    'width' => $toArrayState['width'],
                    'height' => $toArrayState['height'],
                    'num_of_mines' => $toArrayState['numOfMines'],
                    'game_id' => $gameId,
                    'tile_states' => $toArrayState['tileStates'],
                    'is_game_started' => $toArrayState['isGameStarted'],
                    'is_game_clear' => $toArrayState['isGameClear'],
                    'is_game_over' => $toArrayState['isGameOver'],
                ]
            );
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'saveState');
        }
    }

    public function getState(string $gameId): ?State
    {
        if (GameState::where('game_id', $gameId)->exists()) {
            $stateModel = GameState::where('game_id', $gameId)->first();

            return State::fromPrimitive(
                json_decode($stateModel->tile_states, true),
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
     * @throws Exception
     */
    public function updateState(State $state, string $gameId): void
    {
        try {
            $stateModel = GameState::where('game_id', $gameId)->firstOrFail();
            $toArrayState = $state->toArray();
            $stateModel->update(
                [
                    'width' => $toArrayState['width'],
                    'height' => $toArrayState['height'],
                    'num_of_mines' => $toArrayState['numOfMines'],
                    'game_id' => $gameId,
                    'tile_states' => $toArrayState['tileStates'],
                    'is_game_started' => $toArrayState['isGameStarted'],
                    'is_game_clear' => $toArrayState['isGameClear'],
                    'is_game_over' => $toArrayState['isGameOver'],
                ]
            );
        } catch (ModelNotFoundException $exception) {
            throw new RepositoryException("GameState not found for game_id={$gameId}", 0, $exception);
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'updateState');
        }

    }

    /**
     * @throws Exception
     */
    public function deleteState(string $gameId): void
    {
        try {
            $target = GameState::where('game_id', $gameId)->firstOrFail();
            $target->delete();
        } catch (ModelNotFoundException $exception) {
            throw new RepositoryException("GameState not found for game_id={$gameId}", 0, $exception);
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'deleteState');
        }
    }
}
