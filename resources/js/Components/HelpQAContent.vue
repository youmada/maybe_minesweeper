<script setup lang="ts">
defineProps<{
    title: string;
    content: string[];
}>();
</script>

<template>
    <details
        tabindex="0"
        class="collapse collapse-arrow rounded-none border-b border-b-gray-400 bg-gray-900 shadow-lg"
    >
        <summary class="collapse-title text-center text-lg font-semibold">
            {{ title }}
        </summary>
        <div class="collapse-content">
            <p
                v-for="(paragraph, i) in content"
                :key="i"
                class="text-md-body-1"
            >
                {{ paragraph }}
            </p>
        </div>
    </details>
</template>

<style scoped></style>
