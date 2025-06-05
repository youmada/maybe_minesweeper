<?php

namespace App\Repositories\Interfaces;

use App\Domain\Minesweeper\GameState;
use stdClass;

interface GameRepositoryInterface
{
    public function saveState(GameState $state, string $gameId): void;

    public function getState(string $gameId): ?stdClass;

    public function updateState(GameState $state, string $gameId): void;

    public function deleteState(string $gameId): void;
}
