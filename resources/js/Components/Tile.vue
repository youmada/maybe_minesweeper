<script setup lang="ts">
import { Tile } from '@/custom/domain/mineSweeper';
import { computed } from 'vue';

const props = defineProps<{ tile: Tile }>();
const computedText = computed(() => {
    if (props.tile.isFlag) return '';
    if (props.tile.isOpen && !props.tile.isMine) {
        if (props.tile.adjacentMines === 0) return '';
        return props.tile.adjacentMines;
    }
    return '';
});
</script>
<template>
    <span
        class="tile align-center flex text-center text-lg font-bold"
        :class="{
            open: tile.isOpen,
            flag: tile.isFlag,
            mine: tile.isMine && tile.isOpen,
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
    background: #ea5b6f;
}

.flag {
    background: orange;
}
</style>
