<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import { default as PrimaryButton } from '@/Components/PrimaryButton.vue';
import { Tile, useMineSweeper } from '@/custom/useMineSweaper';
import { router } from '@inertiajs/vue3';
import { computed, Ref, ref, watch } from 'vue';
import BoardTile from './Tile.vue';

const props = defineProps<{
    boardWidth: number;
    boardHeight: number;
    totalMine: number;
}>();

const { board, tiles, numOfOpenTiles, reInstance, startGame } = useMineSweeper(
    props.boardWidth,
    props.boardHeight,
    props.totalMine,
);
const isStartGame = ref(false);
const isGameOver = ref(false);
const isGameClear = ref(false);
const isFlagMode = ref(false);

// 一度に開くことができる最大のタイル数
const limitOfChainOpenTile = Infinity;
// limitOfchainOpenTileと比較してタイル連鎖展開をコントロール
const limitOpenTileCounter = ref(0);

function toggleFlag() {
    return (isFlagMode.value = !isFlagMode.value);
}

function handleClickTile(tile: Tile) {
    if (!isStartGame.value) {
        startGame(tile);
        isStartGame.value = true;
        return;
    }

    if (tile.getTileState().isOpen) return;
    if (isFlagMode.value) {
        tile.toggleFlag();
        return;
    }

    if (tile.getFlagState()) return;
    if (tile.getTileState().isMine) return (isGameOver.value = true);
    chainOpenTile(0, 0, tile, limitOpenTileCounter);
    limitOpenTileCounter.value = 0;
}

function chainOpenTile(diffX: number, diffY: number, tile: Tile, limit: Ref) {
    if (limit.value >= limitOfChainOpenTile) return;
    const isTile = board.value.getTile(tile.x + diffX, tile.y + diffY);
    if (!isTile) return;

    const currTile = tiles.value[tile.x + diffX][tile.y + diffY];
    if (!currTile) return;
    // 1.現在位置を把握
    const tileKey = `${currTile.x},${currTile.y}`;

    // すでにタイルが空いているなら早期リターン
    if (numOfOpenTiles.value.has(tileKey)) return;
    numOfOpenTiles.value.add(tileKey);

    const aroundMines = currTile.checkAroundMines(board.value);
    currTile.openTile();
    currTile.isFlag = false;
    limit.value += 1;

    if (aroundMines !== 0) return;

    // 3.八方向を確認して、それぞれの方向の隣接タイルの周辺地雷数が0の場合に、タイルを展開

    // 4.隣接タイルに地雷が隣接するまで、再帰的に実行する。

    // 現在位置から真上
    chainOpenTile(0, -1, currTile, limit);

    // 現在位置から右上
    chainOpenTile(1, -1, currTile, limit);

    // 現在位置から右
    chainOpenTile(1, 0, currTile, limit);

    // 現在位置から右下
    chainOpenTile(1, 1, currTile, limit);

    // 現在位置から真下
    chainOpenTile(0, 1, currTile, limit);

    // 現在位置から左下
    chainOpenTile(-1, 1, currTile, limit);

    // 現在位置から左
    chainOpenTile(-1, 0, currTile, limit);

    // 現在位置から左上
    chainOpenTile(-1, -1, currTile, limit);
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
        if (numOfOpenTiles.value.size === board.value.numOfDeployableTiles)
            isGameClear.value = true;
    },
);

const restCloseTile = computed(() => {
    return (
        props.boardWidth * props.boardHeight -
        props.totalMine -
        numOfOpenTiles.value.size
    );
});

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
                    <span>{{ restCloseTile }}</span>
                </p>
            </div>
            <PrimaryButton
                :class="{
                    'bg-orange-400': isFlagMode,
                    'text-white': isFlagMode,
                    'hover:bg-orange-400': isFlagMode,
                }"
                :click-fn="toggleFlag"
                >フラグモード</PrimaryButton
            >
        </div>
        <div class="m-auto flex w-fit">
            <div class="flex w-fit flex-col" v-for="horizontalTile in tiles">
                <div v-for="tile in horizontalTile">
                    <BoardTile
                        @click="() => handleClickTile(tile)"
                        :checkFn="() => tile.checkAroundMines(board)"
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
