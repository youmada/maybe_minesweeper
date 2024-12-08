<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
// 全ページ共通の基本的なレイアウト
// 内容：slotを画面中央にして、背景をダークモードにする。
</script>

<template>
    <Head title="Home" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <slot></slot>
        </div>
    </div>
</template>
