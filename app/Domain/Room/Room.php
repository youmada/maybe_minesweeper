<?php

namespace App\Domain\Room;

class Room
{
    public function __construct(
        public readonly string $id,
        private readonly string $name,
        private readonly int $maxPlayers,
        private array $players,
        private readonly bool $isPrivate,
        private readonly string $ownerId,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function canJoinPlayer(): bool
    {
        return $this->getMaxPlayers() > count($this->players);
    }

    public function joinRoom(string $user): bool
    {
        if ($this->canJoinPlayer()) {
            $this->players[] = $user;

            return true;
        }

        return false;
    }

    public function leaveRoom(string $leaveUser): bool
    {
        if ($this->isJoined($leaveUser)) {
            $this->players = array_filter($this->players, fn ($user) => $user !== $leaveUser);
            $this->players = array_values($this->players);

            return true;
        }

        return false;
    }

    public function isJoined(string $user): bool
    {
        return in_array($user, $this->players);
    }
}
