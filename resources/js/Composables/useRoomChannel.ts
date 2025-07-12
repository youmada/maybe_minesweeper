import { useToast } from '@/Composables/useToast';
import { useEchoPresence } from '@laravel/echo-vue';
import { ref } from 'vue';

type Player = {
    id: string;
    joinedAt: string;
    isCurrentTurn: boolean;
};

export function useRoomChannel(roomPublicId: string) {
    const roomPlayers = ref<Player[]>([]);
    const { showToast } = useToast();

    const roomStatus = ref('');

    const { channel, leaveChannel } = useEchoPresence(`room.${roomPublicId}`);
    channel().here((players: Player[]) => {
        roomPlayers.value = players;
    });
    channel().joining((player: Player) => {
        const exists = roomPlayers.value.some((p) => p.id === player.id);
        if (!exists) {
            roomPlayers.value.push(player);
            console.log(roomPlayers.value);
            roomPlayers.value = roomPlayers.value.sort(
                (a, b) =>
                    new Date(a.joinedAt).getTime() -
                    new Date(b.joinedAt).getTime(),
            );
            console.log(roomPlayers.value);
        }
    });
    channel().leaving((player: Player) => {
        roomPlayers.value = roomPlayers.value.filter((p) => p.id !== player.id);
    });
    channel().error(() => {
        showToast(
            '現在通信エラーが発生しています。ブラウザをリロードしてください。',
        );
    });

    channel().listen('RoomStatus', (e: any) => {
        roomStatus.value = e.status;
        console.log(e);
    });

    return {
        roomPlayers,
        roomStatus,
        leaveChannel,
    };
}
