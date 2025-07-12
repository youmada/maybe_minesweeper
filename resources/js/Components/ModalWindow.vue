<script setup lang="ts">
const props = defineProps<{ color?: string; modalTile?: String }>();
</script>
<template>
    <div class="fixed inset-0 z-10 bg-white/30 backdrop-blur-md">
        <div
            class="fixed left-1/2 top-1/2 z-20 h-1/2 w-1/2 -translate-x-1/2 -translate-y-1/2 transform rounded-lg bg-gray-900 shadow-md"
        >
            <div class="flex h-full flex-col items-center justify-center">
                <p
                    class="top-7 my-5 text-center text-4xl font-bold"
                    :style="{ color: color }"
                >
                    {{ modalTile }}
                </p>
                <slot></slot>
            </div>
        </div>
    </div>
</template>
