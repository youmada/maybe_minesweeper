<script setup lang="ts">
import HelpIcon from '@/Components/HelpIcon.vue';
import HelpModal from '@/Components/HelpModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { topPageHelpContents } from '@/data';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showHelpModal = ref(false);
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
    <div class="fixed bottom-5 right-5">
        <HelpIcon
            size="size-10"
            @clickHelpIcon="() => (showHelpModal = true)"
        />
    </div>
    <HelpModal
        :isShow="showHelpModal"
        :closeFn="() => (showHelpModal = false)"
        tile="遊びかた"
        :qaContents="topPageHelpContents"
    />
</template>
