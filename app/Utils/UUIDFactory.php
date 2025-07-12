<?php

namespace App\Utils;

use Illuminate\Support\Str;

class UUIDFactory
{
    public static function generate(): string
    {
        return Str::uuid()->toString();
    }

    public static function random($length = 16)
    {
        return Str::random($length);
    }
}
