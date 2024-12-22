<script setup lang="ts">
import Board from '@/Components/Board.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface modeInfo {
    modeName: string;
    boardWidth: number;
    boardHeight: number;
    totalMine: number;
}

const props = defineProps<{ level?: string }>();
const isError = ref(false);
const modes: { [name: string]: modeInfo } = {
    easy: {
        modeName: 'いーじー',
        boardWidth: 10,
        boardHeight: 10,
        totalMine: 30,
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

const currentLevel = computed<modeInfo | null>(() => {
    const level = props.level?.toString() || '';
    if (!modes[level]) {
        isError.value = true; // エラーフラグを立てる
        return null;
    }
    isError.value = false;
    return modes[level] || '';
});
function returnToHome() {
    return router.visit('/');
}
</script>
<template>
    <div class="flex h-full w-full flex-col">
        <!-- エラー時のレイアウト -->
        <template v-if="isError">
            <div class="p-7 text-center">
                <h1 class="text-4xl font-extrabold text-red-500">
                    無効なレベルが指定されました
                </h1>
                <p class="mt-4 text-white">
                    ホームに戻って再度お試しください。
                </p>
                <PrimaryButton :click-fn="returnToHome"
                    >ホームに戻る</PrimaryButton
                >
            </div>
        </template>
        <template v-else-if="currentLevel">
            <div class="flex h-full w-full flex-col">
                <div class="p-7 text-center">
                    <h1 class="text-4xl font-extrabold text-white">
                        {{ currentLevel?.modeName }}
                    </h1>
                </div>
                <Board
                    :board-width="currentLevel?.boardWidth"
                    :board-height="currentLevel?.boardHeight"
                    :totalMine="currentLevel?.totalMine"
                ></Board>
                <div class="p-7 text-center">
                    <PrimaryButton :click-fn="returnToHome"
                        >ホームに戻る</PrimaryButton
                    >
                </div>
            </div>
        </template>
    </div>
</template>
