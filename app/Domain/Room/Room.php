<?php

namespace App\Domain\Room;

class Room
{
    public function __construct(
        private readonly string $name,
        private readonly int $maxPlayer,
        private array $players,
        private string $expireAt,
        private readonly bool $isPrivate,
        private readonly ?string $ownerId,
    ) {}

    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
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

    /**
     *  責任範囲：
     *  一度参加したプレイヤーはプレイヤー上限に引っかからない
     */
    public function canJoinPlayer(string $playerId): bool
    {
        if ($this->isJoined($playerId)) {
            return true;
        }

        return $this->getMaxPlayer() > count($this->players);
    }

    public function joinRoom(string $playerId): bool
    {
        // すでに参加しているので、playersに追加しない
        if ($this->isJoined($playerId)) {
            return true;
        }
        if ($this->canJoinPlayer($playerId)) {
            $this->players[] = $playerId;

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

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'maxPlayer' => $this->maxPlayer,
            'players' => $this->players,
            'expireAt' => $this->expireAt,
            'isPrivate' => $this->isPrivate,
            'ownerId' => $this->ownerId,
        ];
    }

    public static function fromArray(array $attrs): Room
    {
        return new self(
            $attrs['name'],
            $attrs['maxPlayer'],
            $attrs['players'],
            $attrs['expireAt'],
            $attrs['isPrivate'],
            $attrs['ownerId'],
        );
    }
}
