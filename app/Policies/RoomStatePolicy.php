<?php

namespace App\Policies;

use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState;
use App\Models\Player;
// use App\Models\RoomState;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomStatePolicy
{
    use HandlesAuthorization;

    //    public function viewAny(Player $user): bool {}

    //    public function view(Player $user, RoomState $roomState): bool {}

    //    public function create(Player $user): bool {}

    public function update(Player $user, RoomAggregate $roomAggregate): bool
    {
        if ($roomAggregate->getRoomStatus() === 'finished') {
            return false;
        }
        if ($roomAggregate->getRoomStatus() === 'waiting') {
            return false;
        }

        return $user->public_id === $roomAggregate->getCurrentOrder();
    }

    //    public function delete(Player $user, RoomState $roomState): bool {}

    //    public function restore(Player $user, RoomState $roomState): bool {}

    //    public function forceDelete(Player $user, RoomState $roomState): bool {}
}
