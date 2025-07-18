import { useEcho } from '@laravel/echo-vue';
import { computed, ref } from 'vue';

type RoomData = {
    status: string;
};

export function useRoomStatus(roomPublicId: string) {
    const { channel } = useEcho(`room.${roomPublicId}.data`, ['FetchRoomData']);
    const roomState = ref<RoomData>({
        status: '',
    });
    channel().listen('FetchRoomData', (data: RoomData) => {
        console.log(data);
        roomState.value = data;
    });

    const isRoomReady = computed(() => {
        return (
            roomState.value.status === 'playing' ||
            roomState.value.status === 'standby'
        );
    });

    return {
        roomState,
        isRoomReady,
    };
}
