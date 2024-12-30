<script setup lang="ts">
import Board from '@/Components/Board.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useGameStore } from '@/stores/gameStore';
import { useSaveDataStore } from '@/stores/singlePlayData';
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface modeInfo {
    modeName: string;
    boardWidth: number;
    boardHeight: number;
    totalMine: number;
}

onMounted(() => {
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
        const baordData = saveDataStore.getSaveData.board;
        const arrayToVisitedTiles =
            saveDataStore.getSaveData.arrayToVisitedTiles;
        const totalMine = saveDataStore.getSaveData.numOfMines;

        // コンティニューゲーム
        gameStore.continueGame(baordData, arrayToVisitedTiles, totalMine);
    } else {
        // easy / normal / hard の場合
        const currLevel = currentLevel(level);
        if (!currLevel) return; // ここで isError=true がセットされる

        const { boardWidth, boardHeight, totalMine } = currLevel;

        gameStore.initiaraize(boardWidth, boardHeight);
        gameStore.numOfMines = totalMine;
    }
    // 初期化完了
    isInitiaraized.value = true;
});

const gameStore = useGameStore();

const props = defineProps<{ level?: string }>();
const isError = ref(false);
const isInitiaraized = ref(false);
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
</script>
<template>
    <div class="flex h-full w-full flex-col">
        <template v-if="isInitiaraized">
            <div class="flex h-full w-full flex-col">
                <div class="p-7 text-center">
                    <h1 class="text-4xl font-extrabold text-white">
                        {{ modeName }}
                    </h1>
                </div>
                <Board></Board>
                <div class="p-7 text-center">
                    <template v-if="gameStore.isGameStarted">
                        <PrimaryButton class="m-5" :click-fn="onSaveData"
                            >ボードをセーブする</PrimaryButton
                        >
                    </template>
                    <PrimaryButton class="m-5" :click-fn="returnToHome"
                        >ホームに戻る</PrimaryButton
                    >
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
                    >ホームに戻る</PrimaryButton
                >
            </div>
        </template>
    </div>
</template>
