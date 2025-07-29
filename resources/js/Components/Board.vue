<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import { default as PrimaryButton } from '@/Components/PrimaryButton.vue';
import BoardTile from '@/Components/Tile.vue';
import { useGameStore } from '@/stores/gameStore';
import { useSaveDataStore } from '@/stores/singlePlayData';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

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

console.log(gameStore.board);
</script>

<template>
    <div class="w-full">
        <div
            v-if="gameStore.isGameStarted"
            class="m-5 mx-auto flex w-fit rounded-2xl border-2 border-gray-500 p-5"
        >
            <div class="m-2 mr-4 flex justify-around">
                <p class="inline text-center text-2xl font-bold">
                    <span> 残りタイル数 </span>
                    <span>{{ restOpenTiles }}</span>
                </p>
            </div>
            <PrimaryButton
                :class="{
                    'bg-orange-400': gameStore.isFlagMode,
                    'text-white': gameStore.isFlagMode,
                    'hover:bg-orange-400': gameStore.isFlagMode,
                }"
                :clickFn="() => gameStore.toggleFlagMode()"
                >フラグモード
            </PrimaryButton>
        </div>
        <div class="m-auto flex w-fit flex-col">
            <div
                class="flex w-fit"
                v-for="(verticalTile, y) in gameStore.board"
                :key="`row-${y}`"
            >
                <div v-for="(tile, x) in verticalTile" :key="`col-${x}`">
                    <BoardTile
                        @click="() => gameStore.handleClickTile(tile.x, tile.y)"
                        :tile="tile"
                    ></BoardTile>
                </div>
            </div>
        </div>
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
        v-if="gameStore.isGameOver"
        modalTile="ゲームオーバー"
        color="red"
    >
        <PrimaryButton :clickFn="() => restartGame()"
            >もう一度プレイ！
        </PrimaryButton>
    </ModalWindow>
</template>
