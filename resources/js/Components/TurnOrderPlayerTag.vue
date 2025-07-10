<script setup lang="ts">
const props = defineProps<{
    name: string;
    isCurrentTurn: boolean;
}>();
</script>
<template>
    <div
        class="h-auto w-48 rounded-md text-center"
        :class="isCurrentTurn ? 'bg-gray-200' : 'bg-gray-500'"
    >
        <p class="p-4 text-lg font-bold text-black">
            {{ name }}
        </p>
    </div>
</template>
