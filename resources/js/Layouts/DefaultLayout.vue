<script setup lang="ts">
// 全ページ共通の基本的なレイアウト
// 内容：slotを画面中央にして、背景をダークモードにする。
import Toast from '@/Components/Toast.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="bg-base-200 text-white/50">
        <div class="navbar fixed z-50 max-h-16 bg-base-100 shadow-sm">
            <Link href="/">
                <h1 class="btn btn-ghost text-xl">たぶんマインスイーパー</h1>
            </Link>
        </div>
        <div
            class="relative flex min-h-screen flex-col items-center justify-center pt-16"
        >
            <slot></slot>
        </div>
    </div>
    <Toast />
</template>
