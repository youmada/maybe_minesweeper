<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useSaveDataStore } from '@/stores/singlePlayData';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const saveDataStore = useSaveDataStore();

// セーブデータがあるかどうか
const hasSaveData = computed(() => {
    saveDataStore.loadSaveData();
    return saveDataStore.saveData !== null;
});
</script>

<template>
    <Head title="シングルプレイモード選択" />
    <div class="relative flex h-[50vh] w-[50vw] items-center justify-center">
        <div class="flex h-full w-full flex-col items-center">
            <h1
                class="size-fit w-full text-center text-4xl font-bold text-slate-50"
            >
                シングルプレイ
            </h1>
            <main>
                <div
                    class="flex h-full w-full flex-col items-center justify-center"
                >
                    <div class="mb-auto mt-10 flex space-x-4">
                        <Link href="/single/play?level=easy">
                            <PrimaryButton>いーじー</PrimaryButton>
                        </Link>
                        <Link href="/single/play?level=normal">
                            <PrimaryButton>のーまる</PrimaryButton>
                        </Link>
                        <Link href="/single/play?level=hard">
                            <PrimaryButton>はーど</PrimaryButton>
                        </Link>
                    </div>
                    <div v-if="hasSaveData" class="mt-10">
                        <Link href="/single/play?level=continue">
                            <PrimaryButton>こんてぃにゅ〜</PrimaryButton>
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
