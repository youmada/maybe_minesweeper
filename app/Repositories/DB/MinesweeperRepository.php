<?php

namespace App\Repositories\DB;

use App\Domain\Minesweeper\GameState as State;
use App\Exceptions\RepositoryException;
use App\Models\GameState;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Exception;
use Illuminate\Database\QueryException;
use stdClass;

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
            GameState::create(
                [
                    'width' => $state->getWidth(),
                    'height' => $state->getHeight(),
                    'num_of_mines' => $state->getNumOfMines(),
                    'game_id' => $gameId,
                    'tile_states' => json_encode($state->getGameState()),
                    'is_game_started' => $state->isGameStarted(),
                    'is_game_clear' => $state->isGameClear(),
                    'is_game_over' => $state->isGameOver(),
                ]
            );
        } catch (QueryException $exception) {
            throw RepositoryException::fromQueryException($exception, 'saveState');
        }
    }

    public function getState(string $gameId): ?stdClass {}

    /**
     * @throws Exception
     */
    public function updateState(State $state, string $gameId): void {}

    /**
     * @throws Exception
     */
    public function deleteState(string $gameId): void {}
}
