<?php

namespace App\Domain\Room;

enum RoomStatus: string
{
    case WAITING = 'waiting';
    case STANDBY = 'standby';
    case PLAYING = 'playing';
    case FINISHED = 'finished';

}
