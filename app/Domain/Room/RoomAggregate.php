<?php

namespace App\Domain\Room;

use App\Domain\Minesweeper\TileActionMode;
use App\Exceptions\RoomException;
use App\Factories\RoomStateFactory;

class RoomAggregate
{
    public readonly string $roomId;

    public readonly string $roomName;

    public readonly int $maxPlayers;

    public readonly array $players;

    public readonly bool $isPrivate;

    public readonly string $ownerId;

    public readonly int $flagLimit;

    private Room $room;

    private RoomState $roomState;

    public function __construct(
        string $roomId,
        string $roomName,
        int $maxPlayers,
        array $players,
        bool $isPrivate,
        string $ownerId,
        int $flagLimit
    ) {
        $this->roomId = $roomId;
        $this->roomName = $roomName;
        $this->maxPlayers = $maxPlayers;
        $this->players = $players;
        $this->isPrivate = $isPrivate;
        $this->ownerId = $ownerId;
        $this->flagLimit = $flagLimit;
    }

    public function createRoom(): void
    {
        $this->room = new Room($this->roomId, $this->roomName, $this->maxPlayers, $this->players, $this->isPrivate, $this->ownerId);
        $this->roomState = RoomStateFactory::createNew($this->roomId, [], $this->flagLimit);
    }

    public function startRoom(): void
    {
        $this->roomState->initializeTurnOrder($this->room->getPlayers());
        $this->roomState->changeStatus(RoomStatus::PLAYING);
    }

    public function endRoom(): void
    {
        $this->roomState->changeStatus(RoomStatus::FINISHED);
    }

    public function join(string $userId): void
    {
        if (! $this->room->canJoinPlayer()) {
            throw RoomException::playerException('ルーム上限人数に達しました');
        }
        $this->room->joinRoom($userId);
        $this->roomState->pushTurnOrder($userId);
    }

    public function leave(string $userId): void
    {
        if (! $this->room->isJoined($userId)) {
            throw RoomException::PlayerException('ユーザーが存在しません');
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
