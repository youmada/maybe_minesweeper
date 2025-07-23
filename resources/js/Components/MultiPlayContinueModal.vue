<script setup lang="ts">
import ModalWindow from '@/Components/ModalWindow.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps<{
    title: string;
    color: string;
    continueFn: () => Promise<void>;
    leaveRoomFn: () => Promise<void>;
}>();
</script>

<template>
    <ModalWindow :modalTile="title" :color="color">
        <div class="m-4 flex flex-col">
            <PrimaryButton class="m-4" @click="continueFn"
                >もう一度プレイする！
            </PrimaryButton>
            <PrimaryButton class="m-4" @click="leaveRoomFn"
                >ルームから抜ける</PrimaryButton
            >
        </div>
    </ModalWindow>
</template>
