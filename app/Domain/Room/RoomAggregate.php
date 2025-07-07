<?php

namespace App\Domain\Room;

use App\Domain\Minesweeper\TileActionMode;
use App\Exceptions\RoomException;

class RoomAggregate
{
    public function __construct(
        private Room $room,
        private RoomState $roomState,
    ) {}

    public function startRoom(): void
    {
        $this->roomState->initializeTurnOrder($this->room->getPlayers());
        $this->roomState->changeStatus(RoomStatus::PLAYING);
    }

    public function endRoom(): void
    {
        $this->roomState->changeStatus(RoomStatus::FINISHED);
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function getRoomState(): RoomState
    {
        return $this->roomState;
    }

    public function join(string $playerId): void
    {
        if (! $this->room->canJoinPlayer($playerId)) {
            throw RoomException::playerException('ルーム上限人数に達しました');
        }
        $this->room->joinRoom($playerId);
        $this->roomState->pushTurnOrder($playerId);
    }

    public function leave(string $userId): void
    {
        if (! $this->room->isJoined($userId)) {
            throw RoomException::PlayerException('プレイヤーが存在しません');
        }
        $this->room->leaveRoom($userId);
        $this->roomState->removeTurnOrder($userId);
    }

    public function getPlayers(): array
    {
        return $this->room->getPlayers();
    }

    public function getTurnOrder(): array
    {
        return $this->roomState->getTurnOrder();
    }

    public function getCurrentOrder(): string
    {
        return $this->roomState->getCurrentOrder();
    }

    public function getRoomStatus(): string
    {
        return $this->roomState->getStatus();
    }

    public function operate(string $userId, TileActionMode $actionMode): void
    {
        if (! $this->canOperate($userId)) {
            throw RoomException::operationNotAllowed('このターンで操作はできません');
        }
        $this->roomState->processRoomAction($actionMode);
    }

    private function canOperate(string $userId): bool
    {
        if ($this->roomState->getStatus() === RoomStatus::FINISHED->value) {
            return false;
        }
        if ($this->roomState->getStatus() === RoomStatus::WAITING->value) {
            return false;
        }
        if ($this->roomState->canOperate($userId) === false) {
            return false;
        }
        if ($this->roomState->isMoveToNextTurn()) {
            return false;
        }

        return true;
    }

    public function nextTurn(): void
    {
        $this->roomState->nextTurn();
    }

    public function isTurnFinished(): bool
    {
        return $this->roomState->isMoveToNextTurn();
    }
}
