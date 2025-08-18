<script setup lang="ts">
import HelpIcon from '@/Components/HelpIcon.vue';
import HelpModal from '@/Components/HelpModal.vue';
import MagicLinkButton from '@/Components/MagicLinkButton.vue';
import MultiPlayContinueModal from '@/Components/MultiPlayContinueModal.vue';
import MultiPlayStandbyModal from '@/Components/MultiPlayStandbyModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Tile from '@/Components/Tile.vue';
import TurnOrderPlate from '@/Components/TurnOrderPlate.vue';
import { useElementObserver } from '@/Composables/useElementObserver';
import { useGameStateChannel } from '@/Composables/useGameStateChannel';
import { useMinesweeper } from '@/Composables/useMInesweeper';
import { useRoomChannel } from '@/Composables/useRoomChannel';
import { useRoomState } from '@/Composables/useRoomState';
import { useRoomStatus } from '@/Composables/useRoomStatus';
import { multiRoomHelpContents } from '@/data';
import useToastStore from '@/stores/notificationToast';
import { GameState, RoomData } from '@/types/inertiaProps';
import { Head, router } from '@inertiajs/vue3';
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
const showHelpModal = ref(false);
const observerTarget = ref<HTMLElement | null>(null);
let isFirstClicking = false;
let heartBeat: ReturnType<typeof setInterval>;

const { isVisible } = useElementObserver(observerTarget);
const { popUpToast } = useToastStore();
const { roomPlayers, leaveChannel, changeCurrentPlayer } = useRoomChannel(
    roomData.publicId,
    props.auth.user.public_id,
);
const { status } = useRoomStatus(roomData.publicId);
const { roomState } = useRoomState(roomData.publicId, changeCurrentPlayer);
const {
    startGame,
    settingMultiPlay,
    handleFirstClickAction,
    handleFlagAction,
    handleOpenAction,
} = useMinesweeper();

const restTiles = computed(() => {
    const totalTiles = gameData.width * gameData.height;
    return totalTiles - gameData.visitedTiles;
});

const handleKeyup = (e: KeyboardEvent) => {
    if (roomData.status !== 'playing') return;
    if (e.key === 'f') {
        e.preventDefault();
        isFlagMode.value = !isFlagMode.value;
    }
};

onMounted(async () => {
    window.addEventListener('keyup', handleKeyup);
    settingMultiPlay(roomData.publicId);
    useGameStateChannel(roomData.publicId, gameData);
    heartBeat = setInterval(() => {
        axios.put(`/multi/rooms/${roomData.publicId}/play/heartbeat`, {
            room_id: roomData.publicId,
            player_id: props.auth.user.public_id,
        });
    }, 10000); // 10秒ごと
});
onUnmounted(() => {
    window.addEventListener('keyup', handleKeyup);
    leaveChannel(true);
    clearInterval(heartBeat);
});
watch(roomState, (newValue) => {
    if (newValue.data && newValue) {
        roomData.turnActionState.flagCount =
            newValue.data.turnActionState.flagCount;
        roomData.turnActionState.flagLimit =
            newValue.data.turnActionState.flagLimit;
    }
});

watch(status, (newValue) => {
    roomData.status = newValue.room.status;
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

    if (roomData.status === 'standby') {
        if (isFirstClicking) return;
        isFirstClicking = true;

        try {
            const resData = await handleFirstClickAction(x, y);
            gameData.tileStates = JSON.parse(
                JSON.stringify(resData.tileStates),
            );
            const {
                // eslint-disable-next-line @typescript-eslint/no-unused-vars
                tileStates, // 除外
                ...rest
            } = resData;
            Object.assign(gameData, rest);
        } catch (e: any) {
            isFirstClicking = false;
        }
        return;
    }

    if (isFlagMode.value) {
        await handleFlagAction(x, y);
    } else {
        await handleOpenAction(x, y);
    }
};

const handleContinueGame = async () => {
    const response = await axios.post(
        `/multi/rooms/${roomData.publicId}/play/continue`,
    );

    const resData = response.data.data;
    gameData.tileStates = JSON.parse(JSON.stringify(resData.tileStates));
    const {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        tileStates, // 除外
        ...rest
    } = resData;
    Object.assign(gameData, rest);
    isFirstClicking = false;
};

const handleLeaveRoom = async () => {
    router.visit('/');
};

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
    <Head title="マルチプレイ"></Head>
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
    <template v-if="roomData.status === 'waiting'">
        <MultiPlayStandbyModal
            :roomData="roomData"
            :roomPlayers="roomPlayers"
            :handleGameStart="handleGameStart"
            :clipBoard="clipBoard"
            :playButtonText="playButtonText"
        />
    </template>
    <template
        v-if="roomData.status === 'standby' || roomData.status === 'playing'"
    >
        <div
            class="w-full"
            :class="roomData.status === 'playing' ? 'h-[110vh]' : 'h-auto'"
        >
            <div class="flex h-auto justify-center">
                <div class="m-auto">
                    <div ref="observerTarget" class="h-1"></div>
                    <!-- 監視用の透明なダミー -->
                    <div
                        v-if="gameData.isGameStarted"
                        class="w-fit rounded-2xl border-2 border-gray-500"
                        :class="
                            isVisible
                                ? 'm-5 mx-auto flex p-5'
                                : 'fixed bottom-10 left-5 p-1'
                        "
                    >
                        <div class="m-2 flex justify-around">
                            <p
                                class="flex items-center text-center text-2xl font-bold"
                            >
                                <span> 残りタイル数 </span>
                                <span class="ml-2">{{ restTiles }}</span>
                            </p>
                        </div>
                        <div
                            :class="
                                isVisible
                                    ? 'flex-col'
                                    : 'flex-row justify-around'
                            "
                            class="m-3 flex items-center"
                        >
                            <div class="mb-1 flex text-lg font-bold">
                                <span>{{
                                    roomData.turnActionState.flagCount
                                }}</span>
                                <span>/</span>
                                <span>{{
                                    roomData.turnActionState.flagLimit
                                }}</span>
                            </div>
                            <PrimaryButton
                                class="h-1/2"
                                :class="[
                                    isFlagMode
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-gray-300 text-gray-500',
                                ]"
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

                    <!--        ボード本体         -->
                    <div class="m-auto flex w-fit flex-col">
                        <div
                            class="flex w-fit"
                            v-for="(
                                verticalTile, rowIndex
                            ) in gameData.tileStates"
                            :key="`row-${rowIndex}`"
                        >
                            <div
                                v-for="(tile, colIndex) in verticalTile"
                                :key="`col-${colIndex}`"
                            >
                                <Tile
                                    @click="
                                        () => handleClickTile(tile.x, tile.y)
                                    "
                                    :tile="tile"
                                ></Tile>
                            </div>
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
                    <HelpIcon @clickHelpIcon="() => (showHelpModal = true)" />
                    <HelpModal
                        :isShow="showHelpModal"
                        :closeFn="() => (showHelpModal = false)"
                        tile="遊びかた"
                        :qaContents="multiRoomHelpContents"
                    />
                </div>
            </div>
        </div>
    </template>
</template>
