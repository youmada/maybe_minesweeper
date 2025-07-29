<?php

namespace App\Domain\Minesweeper;

enum TileActionMode: string
{
    case OPEN = 'open';
    case FLAG = 'flag';

}
