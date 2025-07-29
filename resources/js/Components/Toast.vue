<script setup lang="ts">
import useToastStore from '@/stores/notificationToast';
import { storeToRefs } from 'pinia';

const toastStore = useToastStore();
const { toasts } = storeToRefs(toastStore);
</script>

<template>
    <div class="toast toast-end toast-top fixed right-4 top-4 z-50 space-y-2">
        <div
            v-for="toast in toasts"
            :key="toast.id"
            role="alert"
            :class="`alert alert-${toast.type}`"
        >
            <span class="text-base font-bold text-white">{{
                toast.message
            }}</span>
        </div>
    </div>
</template>
