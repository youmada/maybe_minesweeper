<?php

namespace App\Policies;

use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState;
use App\Domain\Room\RoomStatus;
use App\Models\Player;
// use App\Models\RoomState;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomStatePolicy
{
    use HandlesAuthorization;

    //    public function view (Player $user, RoomState $roomState): bool {}

    public function update(Player $user, RoomAggregate $roomAggregate): bool
    {
        if ($roomAggregate->getRoomStatus() === RoomStatus::FINISHED->value) {
            return false;
        }
        if ($roomAggregate->getRoomStatus() === RoomStatus::WAITING->value) {
            return false;
        }

        return $user->public_id === $roomAggregate->getCurrentOrder();
    }

    //    public function delete (Player $user, RoomState $roomState): bool {}

    public function restore(Player $user, RoomAggregate $roomAggregate): bool
    {
        return match ($roomAggregate->getRoomStatus()) {
            RoomStatus::PLAYING->value, RoomStatus::FINISHED->value, RoomStatus::STANDBY->value => false,
            default => true,
        };
    }

    public function continueAction(Player $user, RoomAggregate $roomAggregate): bool
    {
        if ($roomAggregate->getRoomStatus() === RoomStatus::FINISHED->value) {
            return true;
        }

        return false;
    }
}
