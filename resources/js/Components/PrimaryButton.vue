<script setup lang="ts">
const props = defineProps<{
    clickFn?: () => void;
}>();
</script>

<template>
    <button
        @click="() => clickFn && clickFn()"
        class="inline-flex items-center rounded-md border border-transparent text-gray-800 bg-gray-200 px-4 py-2 text-lg font-semibold uppercase tracking-widest transition duration-150 ease-in-out  hover:bg-white"
    >
        <slot />
    </button>
</template>
