<?php

namespace App\Utils;

class UUIDFactory
{
    public static function generate(): string
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }
}
