<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BoardTile from '@/Components/Tile.vue';
import { useElementObserver } from '@/Composables/useElementObserver';
import { useGameStore } from '@/stores/gameStore';
import { useSaveDataStore } from '@/stores/singlePlayData';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface modeInfo {
    modeName: string;
    boardWidth: number;
    boardHeight: number;
    totalMine: number;
}

const isModalVisible = ref(false);
const observerTarget = ref<HTMLElement | null>(null);
const { isVisible } = useElementObserver(observerTarget);
const props = defineProps<{ level?: string }>();
const isError = ref(false);
const isInitialized = ref(false);
const saveDataStore = useSaveDataStore();
const modes: { [name: string]: modeInfo } = {
    easy: {
        modeName: 'いーじー',
        boardWidth: 10,
        boardHeight: 10,
        totalMine: 20,
    },
    normal: {
        modeName: 'のーまる',
        boardWidth: 17,
        boardHeight: 15,
        totalMine: 32,
    },
    hard: {
        modeName: 'はーど',
        boardWidth: 20,
        boardHeight: 30,
        totalMine: 100,
    },
};

const gameStore = useGameStore();
const restOpenTiles = computed(() => {
    return gameStore.closedTilesCountWithoutMine;
});

function restartGame() {
    // クエリパラメータから現在のレベルを取得
    const params = new URLSearchParams(window.location.search);
    const currentLevel = params.get('level');

    // クエリパラメータが continue の場合は前回のレベルを取得して遷移
    if (currentLevel === 'continue') {
        const saveDataStore = useSaveDataStore();
        const previouseLevel = saveDataStore.getSaveData?.previousGameMode;
        if (previouseLevel) {
            router.visit(`/single/play?level=${previouseLevel}`);
            return;
        } else {
            // セーブデータがない場合は難易度選択画面に遷移
            router.visit('/single/play?level=invalid');
            return;
        }
    }
    gameStore.initiaraize(gameStore.width, gameStore.height);
}

const handleKeyup = (e: KeyboardEvent) => {
    if (!gameStore.isGameStarted) return;
    if (e.key === 'f') {
        e.preventDefault();
        gameStore.toggleFlagMode();
    }
};

onMounted(() => {
    window.addEventListener('keyup', handleKeyup);
    // セーブデータがある場合はロードする
    saveDataStore.loadSaveData();

    const level = props.level?.toString() || '';

    if (level === 'continue') {
        // セーブデータがあるか確認
        if (!saveDataStore.getSaveData) {
            isError.value = true;
            return;
        }

        // セーブデータから幅・高さ・地雷数を復元
        const boardData = saveDataStore.getSaveData.board;
        const arrayToVisitedTiles =
            saveDataStore.getSaveData.arrayToVisitedTiles;
        const totalMine = saveDataStore.getSaveData.numOfMines;

        // コンティニューゲーム
        gameStore.continueGame(boardData, arrayToVisitedTiles, totalMine);
    } else {
        // easy / normal / hard の場合
        const currLevel = currentLevel(level);
        if (!currLevel) return; // ここで isError=true がセットされる

        const { boardWidth, boardHeight, totalMine } = currLevel;

        gameStore.initiaraize(boardWidth, boardHeight);
        gameStore.numOfMines = totalMine;
    }
    // 初期化完了
    isInitialized.value = true;
});

onUnmounted(() => {
    window.addEventListener('keydown', handleKeyup);
});

const currentLevel = (level: string) => {
    if (!modes[level]) {
        isError.value = true; // エラーフラグを立てる
        return null;
    }
    isError.value = false;
    return modes[level] || '';
};

function returnToHome() {
    return router.visit('/');
}

function onSaveData() {
    saveDataStore.setSaveData(
        gameStore.board,
        gameStore.numOfMines,
        gameStore.visitedTiles,
        props.level?.toString() || '',
    );
    return router.visit('/');
}

const modeName = currentLevel(props.level?.toString() || '')?.modeName;

// 答え合わせの時にモーダルを閉じるために使う。
watch(
    () => gameStore.isGameOver,
    (newValue) => {
        if (newValue) {
            isModalVisible.value = true;
        }
    },
);
</script>
<template>
    <Head title="シングルプレイ" />
    <div class="flex h-full w-full flex-col">
        <template v-if="isInitialized">
            <div class="flex h-full w-full flex-col">
                <div class="p-7 text-center">
                    <h1 class="text-4xl font-extrabold text-white">
                        {{ modeName }}
                    </h1>
                </div>
                <div class="w-full">
                    <!-- 監視用の透明なダミー -->
                    <div ref="observerTarget" class="h-1"></div>

                    <div
                        v-if="gameStore.isGameStarted"
                        class="rounded-2xl border-2 border-gray-500"
                        :class="
                            isVisible
                                ? 'm-5 mx-auto flex w-fit p-5'
                                : 'fixed bottom-10 left-5'
                        "
                    >
                        <div class="m-2 mr-4 flex justify-around">
                            <p class="inline text-center text-2xl font-bold">
                                <span> 残りタイル数 </span>
                                <span>{{ restOpenTiles }}</span>
                            </p>
                        </div>
                        <PrimaryButton
                            class="flex justify-center"
                            :class="{
                                'm-3 w-[90%]': !isVisible,
                                'bg-orange-400': gameStore.isFlagMode,
                                'text-white': gameStore.isFlagMode,
                                'hover:bg-orange-400': gameStore.isFlagMode,
                            }"
                            :clickFn="() => gameStore.toggleFlagMode()"
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
                    <div class="m-auto flex w-fit flex-col">
                        <div
                            class="flex w-fit"
                            v-for="(verticalTile, y) in gameStore.board"
                            :key="`row-${y}`"
                        >
                            <div
                                v-for="(tile, x) in verticalTile"
                                :key="`col-${x}`"
                            >
                                <BoardTile
                                    @click="
                                        () =>
                                            gameStore.handleClickTile(
                                                tile.x,
                                                tile.y,
                                            )
                                    "
                                    :tile="tile"
                                ></BoardTile>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-7 text-center">
                    <template
                        v-if="gameStore.isGameStarted && !gameStore.isGameOver"
                    >
                        <PrimaryButton class="m-5" :click-fn="onSaveData"
                            >ボードをセーブする
                        </PrimaryButton>
                    </template>
                    <template v-if="!isModalVisible && gameStore.isGameOver">
                        <PrimaryButton
                            class="m-5"
                            :click-fn="() => (isModalVisible = true)"
                            >次のゲームを開始する
                        </PrimaryButton>
                    </template>
                    <PrimaryButton class="m-5" :click-fn="returnToHome"
                        >ホームに戻る
                    </PrimaryButton>
                </div>
            </div>
        </template>
        <!-- エラー時のレイアウト -->
        <template v-else>
            <div class="p-7 text-center">
                <h1 class="text-4xl font-extrabold text-red-500">
                    エラーが発生しました。
                </h1>
                <p class="mt-4 text-white">
                    ホームに戻って再度お試しください。
                </p>
                <PrimaryButton :click-fn="returnToHome"
                    >ホームに戻る
                </PrimaryButton>
            </div>
        </template>
    </div>

    <!-- モーダルエリア -->
    <ModalWindow
        v-if="gameStore.isGameClear"
        modalTile="ゲームクリアおめでとう！！"
        color="#0059ff"
    >
        <PrimaryButton :class="'my-5 w-auto'" :clickFn="() => restartGame()"
            >もう一度プレイ
        </PrimaryButton>
        <PrimaryButton
            :class="'my-5 w-auto'"
            :clickFn="() => router.visit('/single')"
            >難易度をえらぶ
        </PrimaryButton>
        <PrimaryButton :class="'my-5 w-auto'" :clickFn="() => router.visit('/')"
            >タイトルに戻る
        </PrimaryButton>
    </ModalWindow>
    <ModalWindow
        v-if="isModalVisible && gameStore.isGameOver"
        modalTile="ゲームオーバー"
        color="red"
    >
        <PrimaryButton :clickFn="() => restartGame()" class="m-4 w-60"
            >もう一度プレイ！
        </PrimaryButton>
        <PrimaryButton
            :clickFn="() => (isModalVisible = false)"
            class="m-4 w-60"
            >答えを見る！
        </PrimaryButton>
    </ModalWindow>
</template>
