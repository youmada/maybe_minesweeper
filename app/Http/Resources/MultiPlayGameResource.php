<?php

namespace App\Http\Resources;

use App\Models\RoomState;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\Composites\RoomCompositeRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MultiPlayGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roomState = RoomState::where('room_id', $this->id)->first();
        $roomRepository = app(RoomCompositeRepository::class)->get($this->id);
        $roomActionState = $roomRepository->getActionState();
        $gameState = app(GameCompositeRepository::class)->getState($this->id);

        return [
            'room' => [
                'publicId' => $this->public_id,
                'name' => $this->name,
                'maxPlayer' => $this->max_player,
                'ownerId' => $this->owner_id,
                'magicLink' => $this->magicLinkUrl,
                'status' => $roomRepository->getRoomStatus(),
                'turnOrder' => $roomRepository->getTurnOrder(),
                'currentPlayer' => $roomRepository->getCurrentOrder(),
                'turnActionState' => [
                    'flagCount' => $roomActionState['flagCount'],
                    'flagLimit' => $roomRepository->getRoomState()->getFlagLimit(),
                ],
            ],
            'game' => [
                ...$gameState->toClientArray(),
            ],
        ];
    }
}
