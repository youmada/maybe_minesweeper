<script setup lang="ts">
import { getAroundTiles, Tile } from '@/custom/domain/mineSweeper';
import { useGameStore } from '@/stores/gameStore';
import { computed } from 'vue';

const props = defineProps<{ tile: Tile }>();
const gameStore = useGameStore();
const computedText = computed(() => {
    if (props.tile.isFlag) return;
    if (props.tile.isOpen && !props.tile.isMine) {
        const totalAroundTiles = getAroundTiles(
            gameStore.board,
            props.tile.x,
            props.tile.y,
        );
        const totalAroundMine = totalAroundTiles.filter((tile) => tile.isMine);
        if (totalAroundMine.length === 0) return;
        return totalAroundMine.length;
    }
});
</script>
<template>
    <span
        class="tile"
        :class="{
            open: tile.isOpen,
            mine: tile.isMine,
            flag: tile.isFlag,
        }"
        >{{ computedText }}</span
    >
</template>
<style scoped>
.tile {
    background: rgb(128, 128, 128);
    border: 1px solid rgb(59, 59, 59);
    width: 30px;
    height: 30px;
    content: '';
    display: block;
}

.open {
    background: white;
    color: gray;
}

.mine {
    text-align: center;
}

.flag {
    background: orange;
}
</style>
