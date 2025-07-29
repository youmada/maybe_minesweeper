<script setup lang="ts">
defineProps<{
    clickFn?: () => void;
}>();
</script>

<template>
    <button
        @click="() => clickFn && clickFn()"
        class="items-center rounded-md border border-transparent bg-gray-200 px-4 py-2 text-lg font-semibold uppercase tracking-widest text-gray-800 transition duration-150 ease-in-out hover:bg-white"
    >
        <slot />
    </button>
</template>
