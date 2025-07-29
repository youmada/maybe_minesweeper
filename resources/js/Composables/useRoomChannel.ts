import useToastStore from '@/stores/notificationToast';
import { Player } from '@/types/inertiaProps';
import { useEchoPresence } from '@laravel/echo-vue';
import { ref } from 'vue';

export function useRoomChannel(roomPublicId: string, currentPlayerId: string) {
    const leavingTimers = new Map<string, ReturnType<typeof setTimeout>>();
    const roomPlayers = ref<Player[]>([]);
    const { popUpToast } = useToastStore();

    const { channel, leaveChannel } = useEchoPresence(`room.${roomPublicId}`, [
        'RoomPlayerList',
    ]);
    channel().here((players: Player[]) => {
        // `isOwn`フラグを追加してソート
        roomPlayers.value = players
            .map((player) => ({
                ...player,
                isOwn: player.id === currentPlayerId, // プレイヤーが自分か別のプレイヤーか判定
            }))
            .sort(
                (a, b) =>
                    new Date(a.joinedAt).getTime() -
                    new Date(b.joinedAt).getTime(),
            );
    });
    channel().joining((player: Player) => {
        const exists = roomPlayers.value.some((p) => p.id === player.id);
        if (exists) return;
        roomPlayers.value.push({
            ...player,
            isOwn: player.id === currentPlayerId,
            isLeaving: false,
        });

        // ソート
        roomPlayers.value = roomPlayers.value.sort(
            (a, b) =>
                new Date(a.joinedAt).getTime() - new Date(b.joinedAt).getTime(),
        );

        // 離脱タイマーがあればキャンセル
        if (leavingTimers.has(player.id)) {
            clearTimeout(leavingTimers.get(player.id));
            leavingTimers.delete(player.id);
        }
    });
    channel().leaving((player: Player) => {
        player.isLeaving = true;
        const timeoutId = setTimeout(() => {
            leavingTimers.delete(player.id);
            // 30秒経っても戻ってこなければ UI から除去
            roomPlayers.value = roomPlayers.value.filter(
                (p) => p.id !== player.id,
            );
        }, 30000);

        leavingTimers.set(player.id, timeoutId);
    });

    channel().listen('RoomPlayerList', (data: { players: Player[] }) => {
        roomPlayers.value = data.players;
        roomPlayers.value.map((player) => {
            player.isOwn = player.id === currentPlayerId;
        });
    });
    channel().error(() => {
        popUpToast(
            '現在通信エラーが発生しています。ブラウザをリロードしてください。',
            'warning',
        );
    });

    const changeCurrentPlayer = (currentPlayerId: string) => {
        roomPlayers.value = roomPlayers.value.map((player) => {
            if (player.id === currentPlayerId) {
                return {
                    ...player,
                    isCurrentTurn: true,
                };
            } else {
                return {
                    ...player,
                    isCurrentTurn: false,
                };
            }
        });
    };

    return {
        roomPlayers,
        leaveChannel,
        changeCurrentPlayer,
    };
}
