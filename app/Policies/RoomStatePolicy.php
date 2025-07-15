<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\RoomState;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomStatePolicy
{
    use HandlesAuthorization;

    //    public function viewAny(Player $user): bool {}

    //    public function view(Player $user, RoomState $roomState): bool {}

    //    public function create(Player $user): bool {}

    public function update(Player $user, RoomState $roomState): bool
    {
        if ($roomState->status === 'finished') {
            return false;
        }
        if ($roomState->status === 'waiting') {
            return false;
        }

        return $user->public_id === $roomState->current_player;
    }

    //    public function delete(Player $user, RoomState $roomState): bool {}

    //    public function restore(Player $user, RoomState $roomState): bool {}

    //    public function forceDelete(Player $user, RoomState $roomState): bool {}
}
