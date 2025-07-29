<?php

namespace App\Services\Multi;

use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\Log;

class MagicLinkService
{
    public function verify(string $roomId, string $magicLinkToken, string $playerSessionId): bool
    {
        // 受け取ったマジックリンクとルームIDの組み合わせが存在するか、チェック
        try {
            $room = Room::where('magic_link_token', $magicLinkToken)
                ->where('id', $roomId)
                ->firstOrFail();
        } catch (Exception $e) {
            Log::error('MagicLinkService verify error: '.$e->getMessage());

            return false;
        }
        // マジックリンクの期限（ルームの期限でもある）をチェック
        if (! $room->isExpired()) {
            return false;
        }
        // ルームの上限人数をチェック（すでに参加したことがあるユーザーは上限でも、履歴から参加可能）
        if (! $room->isRoomJoined($playerSessionId)) {
            return false;
        }

        return true;
    }
}
