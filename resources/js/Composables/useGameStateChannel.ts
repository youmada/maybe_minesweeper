import { Tile } from '@/custom/domain/mineSweeper';
import useToastStore from '@/stores/notificationToast';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { ref, watch } from 'vue';

type GameState = {
    data: {
        width: number;
        height: number;
        isGameStarted: boolean;
        isGameOver: boolean;
        isGameClear: boolean;
        tileStates: Array<Array<Tile>>;
        visitedTiles: number;
    };
};

export function useGameStateChannel(
    roomPublicId: string,
    gameData: GameState['data'],
) {
    const { channel } = useEcho(`game.${roomPublicId}`, [
        'GameDataApplyClient',
        'GameStatesReflectionSignalEvent',
    ]);
    const gameState = ref<GameState>({
        data: {
            width: 0,
            height: 0,
            isGameStarted: false,
            isGameOver: false,
            isGameClear: false,
            tileStates: [],
            visitedTiles: 0,
        },
    });
    const { popUpToast } = useToastStore();
    channel().listen('GameDataApplyClient', (data: GameState) => {
        const { tileStates, ...rest } = data.data;
        const previousTileStates = gameData.tileStates;

        // クリックしたタイルの差分がレスポンスされるので、該当のタイルだけ更新する
        for (const [yStr, row] of Object.entries(tileStates)) {
            const y = parseInt(yStr);
            for (const [xStr, tile] of Object.entries(row)) {
                const x = parseInt(xStr);
                const prevTile = previousTileStates[y]?.[x];

                if (
                    tile.isFlag !== prevTile?.isFlag ||
                    tile.isOpen !== prevTile?.isOpen
                ) {
                    gameData.tileStates[y][x] = tile;
                }
            }
        }
        Object.assign(gameData, rest);
    });

    channel().listen('GameStatesReflectionSignalEvent', async () => {
        await reflectionGameStates();
    });

    const reflectionGameStates = async () => {
        try {
            const res = await axios.get(
                `/multi/rooms/${roomPublicId}/play/reflection`,
            );
            Object.assign(gameData, res.data.data);
        } catch (error: any) {
            popUpToast('データ取得エラーです。再読み込みしてください', 'error');
        }
    };

    watch(gameState, (newValue) => {
        if (newValue && newValue.data) {
            Object.assign(gameData, newValue.data);
        }
    });
}
