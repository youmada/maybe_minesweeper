<script setup lang="ts">
import MagicLinkButton from '@/Components/MagicLinkButton.vue';
import ModalWindow from '@/Components/ModalWindow.vue';
import { Player, RoomData } from '@/types/inertiaProps';

defineProps<{
    roomPlayers: Player[];
    playButtonText: string;
    roomData: RoomData;
    handleGameStart: () => Promise<void>;
    clipBoard: (link: string) => void;
}>();
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
            <span>{{ roomPlayers.length }}</span>
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
            <MagicLinkButton
                :magicLink="roomData.magicLink"
                :clipBoard="clipBoard"
            ></MagicLinkButton>
        </div>
        <button
            @click="async () => await handleGameStart()"
            class="btn btn-xs m-3 border-gray-500 sm:btn-sm md:btn-md lg:btn-lg xl:btn-xl"
        >
            {{ playButtonText }}
        </button>
    </ModalWindow>
</template>
