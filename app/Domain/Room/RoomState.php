<?php

namespace App\Domain\Room;

class RoomState
{
    private int $currentOrderIndex = 0;

    public function __construct(
        private readonly string $roomId,
        private array $turnOrder,
        private RoomStatus $status
    ) {}

    public function getRoomId(): string
    {
        return $this->roomId;
    }

    public function getTurnOrder(): array
    {
        return $this->turnOrder;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function initializeTurnOrder(array $turnOrder): void
    {
        $this->turnOrder = $turnOrder;
    }

    public function changeStatus(RoomStatus $status): void
    {
        $this->status = $status;
    }

    public function pushTurnOrder(string $user): void
    {
        $this->turnOrder[] = $user;
    }

    public function removeTurnOrder(string $user): void
    {
        $this->turnOrder = array_filter($this->turnOrder, fn ($turn) => $turn !== $user);
        $this->turnOrder = array_values($this->turnOrder);
    }

    public function getCurrentOrder(): string
    {

        return $this->turnOrder[$this->currentOrderIndex];
    }

    public function nextTurn(): void
    {
        if ($this->currentOrderIndex === count($this->turnOrder) - 1) {
            $this->currentOrderIndex = 0;

            return;
        }
        $this->currentOrderIndex++;
    }
}
