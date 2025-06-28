<?php

namespace App\Repositories\Interfaces;

use App\Domain\Minesweeper\GameState;

interface GameRepositoryInterface
{
    public function saveState(GameState $state, string $roomId): void;

    public function getState(string $roomId): ?GameState;

    public function updateState(GameState $state, string $roomId): void;

    public function deleteState(string $roomId): void;
}
