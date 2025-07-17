import { Tile } from '@/custom/domain/mineSweeper';
import { useEcho } from '@laravel/echo-vue';
import { ref, watch } from 'vue';

type GameState = {
    data: {
        width: number;
        height: number;
        isGameStarted: boolean;
        isGameOver: boolean;
        isGameClear: boolean;
        tileStates: Array<Array<Tile>>;
        visitedTiles: Array<Tile>;
    };
};

export function useGameStateChannel(
    roomPublicId: string,
    gameData: GameState['data'],
) {
    const { channel } = useEcho(`game.${roomPublicId}`, [
        'GameDataApplyClient',
    ]);
    const gameState = ref<GameState>({
        data: {
            width: 0,
            height: 0,
            isGameStarted: false,
            isGameOver: false,
            isGameClear: false,
            tileStates: [],
            visitedTiles: [],
        },
    });
    channel().listen('GameDataApplyClient', (data: GameState) => {
        gameState.value = data;
    });

    watch(gameState, (newValue) => {
        if (newValue && newValue.data) {
            Object.assign(gameData, newValue.data);
        }
    });
}
