<?php

namespace App\Http\Resources;

use App\Models\RoomState;
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
        $status = RoomState::where('room_id', $this->id)->first()->status;

        return [
            'publicId' => $this->public_id,
            'name' => $this->name,
            'maxPlayer' => $this->max_player,
            'ownerId' => $this->owner_id,
            'magicLink' => $this->magicLinkUrl,
            'status' => $status,
        ];
    }
}
