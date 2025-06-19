<?php

namespace App\Repositories\Interfaces;

use App\Domain\Minesweeper\GameState;

interface GameRepositoryInterface
{
    public function saveState(GameState $state, string $gameId, string $roomId): void;

    public function getState(string $gameId): ?GameState;

    public function updateState(GameState $state, string $gameId): void;

    public function deleteState(string $gameId): void;
}
