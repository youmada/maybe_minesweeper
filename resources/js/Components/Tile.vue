<script setup lang="ts">
import { Tile } from '@/custom/useMineSweaper';
import { computed } from 'vue';

const props = defineProps<{ tile: Tile; checkFn: Function }>();
const computedText = computed(() => {
    if (props.tile.isFlag) return;
    if (props.tile.isOpen && !props.tile.isMine) {
        const totalAroundMine = props.checkFn();
        if (totalAroundMine === 0) return;
        return totalAroundMine;
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
