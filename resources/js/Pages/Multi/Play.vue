<script setup lang="ts">
import MagicLinkButton from '@/Components/MagicLinkButton.vue';
import MultiPlayContinueModal from '@/Components/MultiPlayContinueModal.vue';
import MultiPlayStandbyModal from '@/Components/MultiPlayStandbyModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BoardTile from '@/Components/Tile.vue';
import TurnOrderPlate from '@/Components/TurnOrderPlate.vue';
import { useGameStateChannel } from '@/Composables/useGameStateChannel';
import { useMinesweeper } from '@/Composables/useMInesweeper';
import { useRoomChannel } from '@/Composables/useRoomChannel';
import { useRoomState } from '@/Composables/useRoomState';
import { useRoomStatus } from '@/Composables/useRoomStatus';
import useToastStore from '@/stores/notificationToast';
import { GameState, RoomData } from '@/types/inertiaProps';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

const props = defineProps<{
    auth: {
        user: {
            id: string;
            public_id: string;
        };
    };
    data: {
        room: RoomData;
        game: GameState;
    };
}>();
const roomData = reactive(props.data.room);
const gameData = reactive(props.data.game);
const isFlagMode = ref(false);

const { popUpToast } = useToastStore();
const { roomPlayers, leaveChannel, changeCurrentPlayer } = useRoomChannel(
    roomData.publicId,
    props.auth.user.public_id,
);
const { isRoomReady } = useRoomStatus(roomData.publicId);
const { roomState } = useRoomState(roomData.publicId, changeCurrentPlayer);
const { startGame, settingMultiPlay, handleFlagAction, handleOpenAction } =
    useMinesweeper();

const restTiles = computed(() => {
    const totalTiles = gameData.width * gameData.height;
    return totalTiles - gameData.visitedTiles;
});

onMounted(async () => {
    settingMultiPlay(roomData.publicId);
    useGameStateChannel(roomData.publicId, gameData);
});
onUnmounted(() => {
    leaveChannel(true);
});
watch(roomState, (newValue) => {
    if (newValue.data && newValue) {
        roomData.turnActionState.flagCount =
            newValue.data.turnActionState.flagCount;
        roomData.turnActionState.flagLimit =
            newValue.data.turnActionState.flagLimit;
    }
});
const playButtonText = computed(() => {
    if (roomPlayers.value.length >= roomData.maxPlayer) {
        return 'ゲームスタート！';
    }
    return '今すぐプレイする';
});

const clipBoard = (link: string) => {
    navigator.clipboard.writeText(link);
    popUpToast('URLをコピーしました');
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

const checkFlagTileOrOpenedTile = (x: number, y: number) => {
    const currentTile = gameData.tileStates[y][x];
    if (isFlagMode.value) return currentTile.isOpen;
    return currentTile.isOpen || currentTile.isFlag;
};

const handleClickTile = async (x: number, y: number) => {
    // フラグ配置の時
    if (checkFlagTileOrOpenedTile(x, y)) {
        popUpToast('操作することができません！', 'warning');
        return;
    }
    if (isFlagMode.value) {
        await handleFlagAction(x, y);
    } else {
        await handleOpenAction(x, y);
    }
};

const handleContinueGame = async () => {
    await axios.post(`/multi/rooms/${roomData.publicId}/play/continue`);
};

const handleLeaveRoom = async () => {
    router.visit('/');
};

const isBoardReady = computed(() => {
    if (isRoomReady.value) return true;
    return roomData.status === 'playing' || roomData.status === 'standby';
});

const gameStatus = computed(() => {
    if (gameData.isGameStarted) {
        if (gameData.isGameClear) {
            return 'game_clear';
        }
        if (gameData.isGameOver) {
            return 'game_over';
        }
    }
    return 'standby';
});
</script>
<template>
    <div>
        <template v-if="gameStatus === 'game_over'">
            <MultiPlayContinueModal
                title="やっちまったぜ！ ゲームオーバーだ"
                color="#D92C54"
                :continueFn="handleContinueGame"
                :leaveRoomFn="handleLeaveRoom"
            />
        </template>
        <template v-if="gameStatus === 'game_clear'">
            <MultiPlayContinueModal
                title="よくやった！ゲームクリアおめでとう！！"
                color="#3D74B6"
                :continueFn="handleContinueGame"
                :leaveRoomFn="handleLeaveRoom"
            />
        </template>
    </div>
    <div v-if="isBoardReady">
        <div class="w-full">
            <div
                v-if="gameData.isGameStarted"
                class="m-5 mx-auto flex w-fit rounded-2xl border-2 border-gray-500 p-5"
            >
                <div class="m-2 flex justify-around">
                    <p class="flex items-center text-center text-2xl font-bold">
                        <span> 残りタイル数 </span>
                        <span class="ml-2">{{ restTiles }}</span>
                    </p>
                </div>
                <div class="m-3 flex flex-col items-center">
                    <div class="mb-1 flex text-lg font-bold">
                        <span>{{ roomData.turnActionState.flagCount }}</span>
                        <span>/</span>
                        <span>{{ roomData.turnActionState.flagLimit }}</span>
                    </div>
                    <PrimaryButton
                        class="h-1/2"
                        :class="{
                            'bg-orange-500 text-white hover:bg-orange-300':
                                isFlagMode,
                            'bg-gray-300 text-gray-500 hover:bg-gray-100':
                                !isFlagMode,
                        }"
                        :clickFn="() => toggleFlagMode()"
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
                                d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"
                            />
                        </svg>
                    </PrimaryButton>
                </div>
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

        <div class="fixed bottom-10 right-5">
            <TurnOrderPlate :players="roomPlayers"></TurnOrderPlate>
            <div class="flex h-auto">
                <p class="flex h-auto items-center">
                    ルーム名：{{ roomData.name }}
                </p>
                <MagicLinkButton
                    :magicLink="roomData.magicLink"
                    :clipBoard="clipBoard"
                ></MagicLinkButton>
            </div>
        </div>
    </div>

    <template v-else>
        <MultiPlayStandbyModal
            :roomData="roomData"
            :roomPlayers="roomPlayers"
            :handleGameStart="handleGameStart"
            :clipBoard="clipBoard"
            :playButtonText="playButtonText"
        />
    </template>
</template>

<style scoped></style>
