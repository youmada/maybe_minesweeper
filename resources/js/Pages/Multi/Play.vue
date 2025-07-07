<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import { computed, reactive, ref } from 'vue';

type RoomData = {
    publicId: string;
    players: string[];
    maxPlayer: number;
    magicLink: string;
};

type Player = {
    sessionId: string;
};
const props = defineProps<{
    data: RoomData;
}>();
const roomData = reactive(props.data);

const playButtonText = computed(() => {
    if (roomPlayer.value.length >= roomData.maxPlayer) {
        return 'ゲームスタート！';
    }
    return '今すぐプレイする';
});

const clipBoard = (link: string) => {
    navigator.clipboard.writeText(link);
    showToast('URLをコピーしました');
};

const toastText = ref('');
const isToastShow = ref(false);
const showToast = (text: string) => {
    isToastShow.value = true;
    toastText.value = text;
    setTimeout(() => {
        isToastShow.value = false;
        toastText.value = '';
    }, 2000);
};

const roomPlayer = ref<Player[]>([]);

Echo.join(`room.${roomData.publicId}`)
    .here((players: Player[]) => {
        roomPlayer.value = players;
    })
    .joining((player: Player) => {
        const alreadyExists = roomPlayer.value.some(
            (p) => p.sessionId === player.sessionId,
        );
        if (alreadyExists) {
            return;
        }
        roomPlayer.value.push(player);
    })
    .leaving((player: Player) => {
        roomPlayer.value = roomPlayer.value.filter(
            (p) => player.sessionId !== p.sessionId,
        );
    })
    .error((error: any) => {
        showToast(
            '現在通信エラーが発生しています。ブラウザをリロードしてください。',
        );
    });
</script>
<template>
    <ModalWindow>
        <h2 class="m-4 text-3xl font-bold text-white">
            他のプレイヤーを待っています
        </h2>

        <!-- ルーム参加人数UI -->
        <div
            class="m-4 flex w-3/6 justify-around text-4xl font-bold text-white"
        >
            <span>現在</span>
            <span>{{ roomPlayer.length }}</span>
            <span>/</span>
            <span>{{ roomData.maxPlayer }}</span>
        </div>
        <div
            class="m-3 flex w-3/6 cursor-pointer justify-around rounded-md border-2 border-solid border-gray-600"
        >
            <p
                @click="() => clipBoard(roomData.magicLink)"
                class="flex w-4/5 items-center overflow-y-clip overflow-x-scroll text-nowrap text-gray-500"
            >
                {{ roomData.magicLink }}
            </p>
            <button
                @click="() => clipBoard(roomData.magicLink)"
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
        <button class="btn btn-xs m-3 sm:btn-sm md:btn-md lg:btn-lg xl:btn-xl">
            {{ playButtonText }}
        </button>
    </ModalWindow>

    <div v-show="isToastShow" class="toast z-40">
        <div class="alert alert-info">
            <span class="text-base font-bold text-white">{{ toastText }}</span>
        </div>
    </div>
</template>

<style scoped></style>
