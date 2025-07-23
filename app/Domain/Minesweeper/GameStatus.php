<?php

namespace App\Domain\Minesweeper;

enum GameStatus: string
{
    case WAITING = 'waiting';
    case IN_PROGRESS = 'in_progress';
    case GAME_OVER = 'game_over';
    case GAME_CLEAR = 'game_clear';

}
