<script setup lang="ts">
import HelpIcon from '@/Components/HelpIcon.vue';
import HelpModal from '@/Components/HelpModal.vue';
import MagicLinkButton from '@/Components/MagicLinkButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { roomHelpContents } from '@/data';
import useToastStore from '@/stores/notificationToast';
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
const showHelpModal = ref(false);

const { popUpToast } = useToastStore();

const clipBoard = (link: string) => {
    navigator.clipboard.writeText(link);
    popUpToast('ルーム参加のURLをコピーしました', 'info');
};
</script>

<template>
    <Head title="マルチプレイ"></Head>
    <div class="flex flex-col">
        <h2 class="m-4 text-center text-lg font-bold">作成ルーム一覧</h2>
        <ul class="list min-h-[400px] rounded-box bg-gray-500 shadow-md">
            <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                <div class="flex w-full justify-around text-white">
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
        :qaContents="roomHelpContents"
    />
</template>
