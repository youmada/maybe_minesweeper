import { useEcho } from '@laravel/echo-vue';
import { computed, ref } from 'vue';

interface Status {
    room: {
        status: string;
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
            status: '',
        },
        game: {
            status: '',
        },
    });
    channel().listen('RoomStatusApplyClient', (data: Status) => {
        status.value = data;
    });

    const isRoomReady = computed(() => {
        return (
            status.value.room.status === 'playing' ||
            status.value.room.status === 'standby'
        );
    });

    return {
        status,
        isRoomReady,
    };
}
