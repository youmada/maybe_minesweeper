<?php

namespace App\Domain\Room;

use Illuminate\Support\Str;

class Room
{
    private readonly string $magicLinkToken;

    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly int $maxPlayer,
        private array $players,
        private readonly bool $isPrivate,
        private readonly string $ownerId,
        ?string $magicLinkToken = null,
    ) {
        // DBからの復元に対応するために設定できるようにする。
        $this->magicLinkToken = $magicLinkToken ?? $this->generateUniqueToken();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMagicLinkToken(): string
    {
        return $this->magicLinkToken;
    }

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

    public function canJoinPlayer(): bool
    {
        return $this->getMaxPlayer() > count($this->players);
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

    public function toArray(): array
    {
        return [
            'roomId' => $this->id,
            'name' => $this->name,
            'maxPlayer' => $this->maxPlayer,
            'magicLinkToken' => $this->magicLinkToken,
            'players' => $this->players,
            'isPrivate' => $this->isPrivate,
            'ownerId' => $this->ownerId,
        ];
    }

    public static function fromArray(array $attrs): Room
    {
        return new self(
            $attrs['roomId'],
            $attrs['name'],
            $attrs['maxPlayer'],
            $attrs['players'],
            $attrs['isPrivate'],
            $attrs['ownerId'],
            $attrs['magicLinkToken'],
        );
    }

    // トークンの一意性を保証するヘルパー
    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (\App\Models\Room::where('magic_link_token', $token)->exists());

        return $token;
    }
}
