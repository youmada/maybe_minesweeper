import useToastStore from '@/stores/notificationToast';
import { Player } from '@/types/inertiaProps';
import { useEchoPresence } from '@laravel/echo-vue';
import { ref } from 'vue';

export function useRoomChannel(roomPublicId: string, currentPlayerId: string) {
    const roomPlayers = ref<Player[]>([]);
    const { popUpToast } = useToastStore();

    const { channel, leaveChannel } = useEchoPresence(`room.${roomPublicId}`);
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
        });

        // ソート
        roomPlayers.value = roomPlayers.value.sort(
            (a, b) =>
                new Date(a.joinedAt).getTime() - new Date(b.joinedAt).getTime(),
        );
    });
    channel().leaving((player: Player) => {
        roomPlayers.value = roomPlayers.value.filter((p) => p.id !== player.id);
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
