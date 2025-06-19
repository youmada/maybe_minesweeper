<?php

namespace App\Domain\Room;

use App\Domain\Minesweeper\TileActionMode;

class RoomState
{
    public function __construct(
        private array $turnOrder,
        private int $currentOrderIndex,
        private RoomStatus $status,
        private int $flagCount, // フラグ処理の合計回数
        private bool $tileOpened, // 1ターン内でタイル展開したかどうか
        private readonly int $flagLimit,
    ) {}

    public static function fromArray(array $attrs): self
    {
        return new self(
            $attrs['turnOrder'],
            $attrs['currentOrderIndex'] ?? 0,
            RoomStatus::from($attrs['status']),
            $attrs['turnActionState']['flagCount'] ?? 0,
            $attrs['turnActionState']['tileOpened'] ?? false,
            $attrs['flagLimit'],
        );
    }

    public function getTurnOrder(): array
    {
        return $this->turnOrder;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function getFlagLimit(): int
    {
        return $this->flagLimit;
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

    public function getCurrentOrder(): ?string
    {
        return $this->turnOrder[$this->currentOrderIndex] ?? null;
    }

    public function nextTurn(): void
    {
        $this->resetActionState();
        if ($this->currentOrderIndex === count($this->turnOrder) - 1) {
            $this->currentOrderIndex = 0;

            return;
        }
        $this->currentOrderIndex++;
    }

    public function processRoomAction(TileActionMode $actionMode): void
    {
        if ($actionMode === TileActionMode::OPEN) {
            $this->tileOpened = true;
        } elseif ($actionMode === TileActionMode::FLAG) {
            $this->flagCount++;
        }
    }

    public function getActionState(): array
    {
        return [
            'tileOpened' => $this->tileOpened,
            'flagCount' => $this->flagCount,
        ];
    }

    private function resetActionState(): void
    {
        $this->tileOpened = false;
        $this->flagCount = 0;
    }

    public function isMoveToNextTurn(): bool
    {
        return $this->tileOpened === true || $this->flagCount === $this->flagLimit;
    }

    public function canOperate(string $user): bool
    {
        return $this->getCurrentOrder() === $user;
    }

    public function toArray(): array
    {
        return [
            'turnOrder' => $this->getTurnOrder(),
            'currentOrderIndex' => $this->currentOrderIndex,
            'status' => $this->getStatus(),
            'turnActionState' => $this->getActionState(),
            'flagLimit' => $this->flagLimit,
        ];
    }
}
