import { Tile } from '@/custom/domain/mineSweeper';
import axios from 'axios';
import { reactive, ref } from 'vue';

type Game = {
    tileStates: Array<Array<Tile>>;
    width: number;
    height: number;
    visitedTiles: Array<Tile>;
    isGameStarted: boolean;
    isGameOver: boolean;
    isGameClear: boolean;
};

export function useMinesweeper() {
    // 内部定数
    const isDuplicateClick = ref(false);

    const gameState = ref<Game>({
        tileStates: [],
        width: 0,
        height: 0,
        visitedTiles: [],
        isGameStarted: false,
        isGameOver: false,
        isGameClear: false,
    });
    const roomConfig = reactive<{ roomId: string | null }>({
        roomId: null,
    });
    const settingMultiPlay = (roomId: string) => {
        roomConfig.roomId = roomId;
    };

    const handleOpenAction = async (x: number, y: number) => {
        const response = await axios.put(
            `/multi/rooms/${roomConfig.roomId}/play/operate`,
            {
                x: x,
                y: y,
                operation: 'open',
            },
        );
        // ここに関してはpiniaストアに通知トースト管理を作成して、呼び出す。
        checkResponseStatus(response);
    };

    const startGame = async (): Promise<boolean> => {
        if (isDuplicateClick.value) return false;
        isDuplicateClick.value = true;
        // 今後リスタートの際にはステータスが必要になる。
        const response = await axios.post(
            `/multi/rooms/${roomConfig.roomId}/play/start`,
        );
        isDuplicateClick.value = false;
        // 正常に処理できない場合は、ゲーム開始しない
        return response.status === 201;
    };

    const handleFlagAction = async (x: number, y: number) => {
        const response = await axios.put(
            `/multi/rooms/${roomConfig.roomId}/play/operate`,
            {
                x: x,
                y: y,
                operation: 'flag',
            },
        );
        checkResponseStatus(response);
    };

    const checkResponseStatus = (response: any) => {
        switch (response.status) {
            case 201:
                console.log('success');
                break;
            case 400:
                console.log('player action is invalid');
                break;
            case 500:
                console.log('server error');
        }
    };
    return {
        handleOpenAction,
        handleFlagAction,
        settingMultiPlay,
        startGame,
    };
}
