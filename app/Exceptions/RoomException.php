<?php

namespace App\Exceptions;

use RuntimeException;

class RoomException extends RuntimeException
{
    public static function PlayerException(string $message): self
    {
        return new self($message);
    }

    public static function RoomException(string $message): self
    {
        return new self($message);
    }

    public static function operationNotAllowed(string $message): self
    {
        return new self($message);
    }
}
