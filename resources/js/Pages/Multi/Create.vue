<script setup lang="ts">
import HelpIcon from '@/Components/HelpIcon.vue';
import HelpModal from '@/Components/HelpModal.vue';
import { roomCreateHelpContents } from '@/data';
import useToastStore from '@/stores/notificationToast';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    boardWidth: 10,
    boardHeight: 10,
    mineRatio: 20,
    expireAt: 1,
    maxPlayer: 3,
});
const showHelpModal = ref(false);

const { popUpToast } = useToastStore();
</script>

<template>
    <Head><title>マルチプレイルーム作成</title></Head>
    <div
        class="flex min-h-screen w-full flex-col items-center justify-center p-4"
    >
        <h2 class="m-4 text-center text-3xl font-bold">ルーム作成</h2>
        <div class="card w-full max-w-lg bg-base-100 shadow-xl">
            <div class="card-body text-base">
                <form
                    @submit.prevent="
                        form.post('/multi/rooms', {
                            onError: () => {
                                Object.entries(form.errors).forEach(
                                    ([key, message]) => {
                                        popUpToast(message, 'error');
                                    },
                                );
                            },
                        })
                    "
                    class="mt-6 space-y-6"
                    :disabled="form.processing"
                >
                    <!--       ルーム名 -->
                    <input
                        name="name"
                        type="text"
                        placeholder="ルーム名"
                        class="input input-lg w-full text-center"
                        v-model="form.name"
                        required
                    />
                    <!--     ボード幅-->
                    <div>
                        <label class="label w-full text-center">
                            <span class="m-auto font-medium"
                                >ボード幅: {{ form.boardWidth }}
                            </span>
                        </label>
                        <input
                            type="range"
                            min="10"
                            max="20"
                            step="5"
                            class="range w-full"
                            v-model="form.boardWidth"
                        />
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>|</span>
                            <span>|</span>
                            <span>|</span>
                        </div>
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>10</span>
                            <span>15</span>
                            <span>20</span>
                        </div>
                    </div>

                    <!-- ボード高さ -->
                    <div class="form-control">
                        <label class="label w-full">
                            <span class="m-auto font-medium"
                                >ボード高さ: {{ form.boardHeight }}</span
                            >
                        </label>
                        <input
                            type="range"
                            min="10"
                            max="20"
                            step="5"
                            class="range w-full"
                            v-model="form.boardHeight"
                        />
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>|</span>
                            <span>|</span>
                            <span>|</span>
                        </div>
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>10</span>
                            <span>15</span>
                            <span>20</span>
                        </div>
                    </div>

                    <!-- 地雷の割合 -->
                    <div class="form-control">
                        <label class="label w-full">
                            <span class="label-text m-auto font-medium"
                                >地雷の割合：{{ form.mineRatio }}(%)</span
                            >
                        </label>
                        <input
                            v-model="form.mineRatio"
                            type="range"
                            min="10"
                            max="40"
                            value="20"
                            class="range range-md w-full"
                            required
                        />
                    </div>

                    <!-- ルームの削除までの時間 -->
                    <div class="form-control">
                        <label class="label w-full">
                            <span class="label-text m-auto font-medium"
                                >ルームの削除までの時間</span
                            >
                        </label>

                        <select
                            class="select w-full text-center"
                            v-model="form.expireAt"
                            required
                        >
                            <option value="1">1日</option>
                            <option value="7">1週間</option>
                            <option value="14">2週間</option>
                        </select>
                    </div>

                    <!-- ルーム参加上限人数 -->
                    <div class="form-control">
                        <label class="label w-full">
                            <span class="label-text m-auto font-medium"
                                >ルーム参加上限人数：{{
                                    form.maxPlayer
                                }}人</span
                            >
                        </label>
                        <input
                            v-model="form.maxPlayer"
                            type="range"
                            min="2"
                            max="6"
                            value="3"
                            class="range range-md w-full"
                            required
                        />
                    </div>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <button
                            type="submit"
                            class="btn btn-outline mx-auto"
                            :disabled="form.processing"
                        >
                            ルームを作成する
                        </button>
                    </div>
                </form>
            </div>
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
        :qaContents="roomCreateHelpContents"
    />
</template>
