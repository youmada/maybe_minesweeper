<?php

namespace App\Factories;

use App\Domain\Room\RoomState;
use App\Domain\Room\RoomStatus;

class RoomStateFactory
{
    public static function createNew(
        array $turnOrder,
        int $flagLimit = 5 // デフォルト値として最大フラグカウントを設定可能
    ): RoomState {
        return new RoomState(
            $turnOrder,
            0,
            RoomStatus::WAITING,
            0,
            false,
            $flagLimit // 新しいプロパティとしてフラグ操作上限を渡す
        );
    }

    public static function createFromRedis(array $data): RoomState
    {
        $turnActionState = $data['turnActionState'];

        return new RoomState(
            $data['turnOrder'],
            $data['currentOrderIndex'],
            RoomStatus::from($data['status']),
            $turnActionState['flagCount'] ?? 0,
            $turnActionState['tileOpened'] ?? false,
            $data['flagLimit']
        );
    }
}
