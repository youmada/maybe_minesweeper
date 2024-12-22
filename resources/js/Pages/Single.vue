<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />
    <div class="relative flex h-[50vh] w-[50vw] items-center justify-center">
        <div class="flex h-full w-full flex-col items-center">
            <h1
                class="size-fit w-full text-center text-4xl font-bold text-slate-50"
            >
                シングルプレイ
            </h1>
            <main class="mb-auto mt-auto flex space-x-4">
                <Link href="/single/play?level=easy">
                    <PrimaryButton>いーじー</PrimaryButton>
                </Link>
                <Link href="/single/play?level=normal">
                    <PrimaryButton>のーまる</PrimaryButton>
                </Link>
                <Link href="/single/play?level=hard">
                    <PrimaryButton>はーど</PrimaryButton>
                </Link>
            </main>
        </div>
    </div>
</template>
