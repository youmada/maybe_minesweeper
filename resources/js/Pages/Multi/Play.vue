<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BoardTile from '@/Components/Tile.vue';
import { useMinesweeper } from '@/Composables/useMInesweeper';
import { useRoomChannel } from '@/Composables/useRoomChannel';
import { useToast } from '@/Composables/useToast';
import { Tile } from '@/custom/domain/mineSweeper';
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';

type RoomData = {
    publicId: string;
    ownerId: string;
    maxPlayer: number;
    magicLink: string;
    status: string;
};

type GameState = {
    width: number;
    height: number;
    numOfMines: number;
    isGameStarted: boolean;
    isGameOver: boolean;
    isGameClear: boolean;
    tileStates: Array<Array<Tile>>;
    visitedTiles: Array<Tile>;
};

const props = defineProps<{
    data: {
        room: RoomData;
        game: GameState;
    };
}>();
const roomData = reactive(props.data.room);
const gameData = reactive(props.data.game);
const isFlagMode = ref(false);

const { showToast, isToastShow, toastText } = useToast();
const { roomPlayers, leaveChannel } = useRoomChannel(roomData.publicId);
const {
    startGame,
    settingMultiPlay,
    gameState,
    handleFlagAction,
    handleTileAction,
} = useMinesweeper();

const restTiles = computed(() => {
    const totalTiles = gameData.width * gameData.height;
    return totalTiles - gameData.visitedTiles.length;
});

onMounted(async () => {
    settingMultiPlay(roomData.publicId);
});
onUnmounted(() => {
    leaveChannel(true);
});

const playButtonText = computed(() => {
    if (roomPlayers.value.length >= roomData.maxPlayer) {
        return 'ゲームスタート！';
    }
    return '今すぐプレイする';
});

const clipBoard = (link: string) => {
    navigator.clipboard.writeText(link);
    showToast('URLをコピーしました');
};
const handleGameStart = async () => {
    // startGameは開始できるかをbooleanで返す
    if (await startGame()) {
        roomData.status = 'standby';
    }
};
const toggleFlagMode = () => {
    isFlagMode.value = !isFlagMode.value;
};

const handleClickTile = (x: number, y: number) => {
    // フラグ配置の時
    if (isFlagMode.value) {
        handleFlagAction(x, y);
    } else {
        handleTileAction(x, y);
    }
};

const isBoardReady = computed(() => {
    return roomData.status === 'playing' || roomData.status === 'standby';
});
</script>
<template>
    <div v-if="isBoardReady">
        <div class="w-full">
            <div
                v-if="gameData.isGameStarted"
                class="m-5 mx-auto flex w-fit rounded-2xl border-2 border-gray-500 p-5"
            >
                <div class="m-2 mr-4 flex justify-around">
                    <p class="inline text-center text-2xl font-bold">
                        <span> 残りタイル数 </span>
                        <span>{{ restTiles }}</span>
                    </p>
                </div>
                <PrimaryButton
                    :class="{
                        'bg-orange-400 text-white hover:bg-orange-400':
                            isFlagMode,
                    }"
                    :clickFn="() => toggleFlagMode()"
                    >フラグモード
                </PrimaryButton>
            </div>
            <div class="m-auto flex w-fit flex-col">
                <div
                    class="flex w-fit"
                    v-for="(verticalTile, rowIndex) in gameData.tileStates"
                    :key="`row-${rowIndex}`"
                >
                    <div
                        v-for="(tile, colIndex) in verticalTile"
                        :key="`col-${colIndex}`"
                    >
                        <BoardTile
                            @click="() => handleClickTile(tile.x, tile.y)"
                            :tile="tile"
                        ></BoardTile>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ModalWindow v-else>
        <h2 class="m-4 text-3xl font-bold text-white">
            他のプレイヤーを待っています
        </h2>

        <!-- ルーム参加人数UI -->
        <div
            class="m-4 flex w-3/6 justify-around text-4xl font-bold text-white"
        >
            <span>現在</span>
            <span>{{ roomPlayers.length }}</span>
            <span>/</span>
            <span>{{ roomData.maxPlayer }}</span>
        </div>
        <div
            class="m-3 flex w-3/6 cursor-pointer justify-around rounded-md border-2 border-solid border-gray-600"
        >
            <p
                @click="() => clipBoard(roomData.magicLink)"
                class="flex w-4/5 items-center overflow-y-clip overflow-x-scroll text-nowrap text-gray-500"
            >
                {{ roomData.magicLink }}
            </p>
            <button
                @click="() => clipBoard(roomData.magicLink)"
                class="btn btn-ghost btn-square"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="size-6"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"
                    />
                </svg>
            </button>
        </div>
        <button
            @click="handleGameStart"
            class="btn btn-xs m-3 border-gray-500 sm:btn-sm md:btn-md lg:btn-lg xl:btn-xl"
        >
            {{ playButtonText }}
        </button>
    </ModalWindow>

    <div v-show="isToastShow" class="toast z-40">
        <div class="alert alert-info">
            <span class="text-base font-bold text-white">{{ toastText }}</span>
        </div>
    </div>
</template>

<style scoped></style>
