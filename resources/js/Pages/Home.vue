<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div
                class="relative flex h-[50vh] w-[50vw] items-center justify-center"
            >
                <div class="flex h-full w-full flex-col items-center">
                    <h1
                        class="size-fit w-full text-center text-4xl font-bold text-slate-50"
                    >
                        まいんすいーぱーおんらいん
                    </h1>
                    <main class="mb-auto mt-auto flex space-x-4">
                        <Link href="/single">
                            <PrimaryButton>シングルプレイ</PrimaryButton>
                        </Link>
                        <Link href="/multi">
                            <PrimaryButton>マルチプレイ</PrimaryButton>
                        </Link>
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
