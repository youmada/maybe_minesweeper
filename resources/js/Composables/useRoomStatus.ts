import { useEcho } from '@laravel/echo-vue';
import { ref } from 'vue';

interface Status {
    room: {
        status: 'waiting' | 'standby' | 'playing' | 'finished';
    };
    game: {
        status: string;
    };
}

export function useRoomStatus(roomPublicId: string) {
    const { channel } = useEcho(`room.${roomPublicId}.data`, [
        'RoomStatusApplyClient',
    ]);
    const status = ref<Status>({
        room: {
            status: 'waiting',
        },
        game: {
            status: '',
        },
    });
    channel().listen('RoomStatusApplyClient', (data: Status) => {
        status.value = data;
    });

    return {
        status,
    };
}
