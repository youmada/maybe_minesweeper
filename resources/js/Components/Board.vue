<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import { default as PrimaryButton } from '@/Components/PrimaryButton.vue';
import { Tile, useMineSweeper } from '@/custom/useMineSweaper';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import BoardTile from './Tile.vue';

const props = defineProps<{
    boardWidth: number;
    boardHeight: number;
    totalMine: number;
}>();

const {
    boardController,
    gameController,
    numOfOpenTiles,
    reInstance,
    startGame,
} = useMineSweeper(props.boardWidth, props.boardHeight, props.totalMine);

const isStartGame = ref(false);
const isGameOver = ref(false);
const isGameClear = ref(false);
const isFlagMode = ref(false);

function handleClickTile(tile: Tile) {
    if (!isStartGame.value) {
        startGame(tile);
        isStartGame.value = true;
        return;
    }

    if (isFlagMode.value) {
        tile.toggleFlag();
        return;
    }

    // タイルが開かれているか、フラグが立っている場合は何もしない
    if (!boardController.value.isOpenTile(tile)) return;
    if (gameController.value.isGameOver(tile)) return (isGameOver.value = true);
    boardController.value.openTile(tile);
}

function gameClear() {
    console.log('you win');
}
function gameOver() {
    console.log('you lose');
}

// 展開できるタイル数が0になった時の処理
watch(
    () => numOfOpenTiles.value.size,
    () => {
        if (gameController.value.isGameClear()) isGameClear.value = true;
    },
);

// ゲームクリア処理
watch(isGameClear, () => {
    if (isGameClear.value === true) gameClear();
});

// ゲームオーバー処理
watch(isGameOver, () => {
    if (isGameOver.value === true) gameOver();
});

function restartGame() {
    isGameOver.value = false;
    isGameClear.value = false;
    isStartGame.value = false;
    reInstance();
}
</script>

<template>
    <div class="w-full">
        <div
            v-if="isStartGame"
            class="m-5 mx-auto flex w-fit rounded-2xl border-2 border-gray-500 p-5"
        >
            <div class="m-2 mr-4 flex justify-around">
                <p class="inline text-center text-2xl font-bold">
                    <span> 残りタイル数 </span>
                    <span>{{ boardController.numOfDeployableTiles }}</span>
                </p>
            </div>
            <PrimaryButton
                :class="{
                    'bg-orange-400': isFlagMode,
                    'text-white': isFlagMode,
                    'hover:bg-orange-400': isFlagMode,
                }"
                :clickFn="() => (isFlagMode = !isFlagMode)"
                >フラグモード</PrimaryButton
            >
        </div>
        <div class="m-auto flex w-fit">
            <div
                class="flex w-fit flex-col"
                v-for="horizontalTile in boardController.getBoard()"
            >
                <div v-for="tile in horizontalTile">
                    <BoardTile
                        @click="() => handleClickTile(tile)"
                        :checkFn="() => boardController.checkAroundMines(tile)"
                        :tile="tile"
                    ></BoardTile>
                </div>
            </div>
        </div>
    </div>

    <!-- モーダルエリア -->
    <ModalWindow
        v-if="isGameClear"
        modalTile="ゲームクリアおめでとう！！"
        color="#0059ff"
    >
        <PrimaryButton :class="'my-5 w-auto'" :clickFn="() => restartGame()"
            >もう一度プレイ</PrimaryButton
        >
        <PrimaryButton
            :class="'my-5 w-auto'"
            :clickFn="() => router.visit('/single')"
            >難易度をえらぶ</PrimaryButton
        >
        <PrimaryButton :class="'my-5 w-auto'" :clickFn="() => router.visit('/')"
            >タイトルに戻る</PrimaryButton
        >
    </ModalWindow>
    <ModalWindow v-if="isGameOver" modalTile="ゲームオーバー" color="red">
        <PrimaryButton :clickFn="() => restartGame()"
            >もう一度プレイ！</PrimaryButton
        >
    </ModalWindow>
</template>
