import { useEcho } from '@laravel/echo-vue';
import { reactive, watch } from 'vue';

type RoomState = {
    data: {
        currentPlayer: string;
        turnActionState: {
            tileOpened: boolean;
            flagCount: number;
            flagLimit: number;
        };
    };
};

export function useRoomState(
    roomPublicId: string,
    turnOrderChangeFn: (playerId: string) => void,
) {
    const { channel } = useEcho(`room.${roomPublicId}.state`, [
        'RoomStateApplyClientEvent',
    ]);
    const roomState = reactive<RoomState>({
        data: {
            currentPlayer: '',
            turnActionState: {
                tileOpened: false,
                flagCount: 0,
                flagLimit: 0,
            },
        },
    });
    channel().listen('RoomStateApplyClientEvent', (response: RoomState) => {
        Object.assign(roomState.data, response.data);
    });

    watch(roomState, (newValue) => {
        if (newValue && newValue.data) {
            turnOrderChangeFn(newValue.data.currentPlayer);
        }
    });
    return {
        roomState,
    };
}
