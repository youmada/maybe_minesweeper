<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="ホーム" />
    <div class="relative flex h-[50vh] w-[50vw] items-center justify-center">
        <div class="flex h-full w-full flex-col items-center">
            <main class="mb-auto mt-auto flex space-x-4">
                <Link href="/single">
                    <PrimaryButton>シングルプレイ</PrimaryButton>
                </Link>
                <Link href="/multi/rooms">
                    <PrimaryButton>マルチプレイ</PrimaryButton>
                </Link>
            </main>
        </div>
    </div>
</template>
