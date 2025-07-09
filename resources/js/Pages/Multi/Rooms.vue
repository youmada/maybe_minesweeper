<script setup lang="ts">
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
                    <button
                        @click.stop="clipBoard(room.magicLink)"
                        class="btn btn-ghost btn-square"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"
                            />
                        </svg>
                    </button>
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
