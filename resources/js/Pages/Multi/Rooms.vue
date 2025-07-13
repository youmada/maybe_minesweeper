<script setup lang="ts">
import MagicLinkButton from '@/Components/MagicLinkButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type Room = {
    name: string;
    expireAt: string;
    magicLink: string;
};

const props = defineProps({
    data: Array<Room>,
});
const rooms = props.data;

const isClipped = ref(false);

const clipBoard = (link: string) => {
    isClipped.value = true;

    navigator.clipboard.writeText(link);

    setTimeout(() => {
        isClipped.value = false;
    }, 2000);
};
</script>

<template>
    <Head title="マルチプレイ"></Head>
    <div class="flex flex-col">
        <h2 class="m-4 text-center text-lg font-bold">作成ルーム一覧</h2>
        <ul class="list min-h-[400px] rounded-box bg-gray-500 shadow-md">
            <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                <div class="flex w-full justify-around">
                    <span class="font-bold">ルーム名</span>
                    <span class="font-bold">有効期限</span>
                    <span class="font-bold">ルームURL取得</span>
                </div>
            </li>
            <li
                v-for="(room, key) in rooms"
                @click="router.get(room.magicLink)"
                :key="key"
                class="list-row m-4 cursor-pointer bg-gray-700"
            >
                <div class="m-3 flex items-center">
                    <span class="text-md font-bold">{{ room.name }}</span>
                </div>
                <div class="m-3 flex items-center">
                    <span class="text-md font-bold">{{ room.expireAt }}</span>
                </div>
                <div class="m-3">
                    <MagicLinkButton
                        :magicLink="room.magicLink"
                        :clipBoard="clipBoard"
                    />
                </div>
            </li>
        </ul>

        <div class="m-16 text-center">
            <Link href="/multi/rooms/create">
                <PrimaryButton>ルーム作成</PrimaryButton>
            </Link>
        </div>
    </div>

    <div v-show="isClipped" class="toast">
        <div class="alert alert-info">
            <span class="text-base font-bold text-white"
                >ルーム参加のURLをコピーしました</span
            >
        </div>
    </div>
</template>

<style scoped></style>
