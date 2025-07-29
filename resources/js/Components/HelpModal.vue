<script setup lang="ts">
import HelpQAContent from '@/Components/HelpQAContent.vue';
import { QAContent } from '@/data';

defineProps<{
    isShow: boolean;
    closeFn: () => void;
    tile: string;
    qaContents: Array<QAContent>;
}>();
</script>

<template>
    <div
        v-if="isShow"
        @click="closeFn"
        class="fixed inset-0 z-10 bg-white/30 backdrop-blur-md"
    >
        <div
            class="fixed left-1/2 top-1/2 z-20 h-2/3 w-2/3 -translate-x-1/2 -translate-y-1/2 transform rounded-lg bg-gray-900 shadow-md"
            @click.stop
        >
            <div class="flex h-full flex-col overflow-y-scroll">
                <h2 class="my-5 text-center text-3xl font-bold">
                    {{ tile }}
                </h2>
                <!-- スクロール可能なコンテンツ部分 -->
                <div class="flex-1 space-y-4 overflow-y-auto pb-6">
                    <template
                        v-for="(qaContent, index) in qaContents"
                        :key="index"
                    >
                        <HelpQAContent
                            :title="qaContent.title"
                            :content="qaContent.content"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
