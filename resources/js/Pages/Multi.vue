<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const boardWidth = ref(10);
const boardHeight = ref(10);
const mineRatio = ref('20');
const roomDuration = ref('1');
const maxPlayers = ref('3');
const roomPermission = ref(false);

const createRoom = () => {
    // ルーム作成のロジックをここに追加
};

const selectPreset = () => {
    // プリセットのボード選択ロジックをここに追加
};
</script>

<template>
    <Head><title>マルチプレイルーム作成</title></Head>
    <div class="bg-base-200 flex min-h-screen items-center justify-center p-4">
        <div class="card bg-base-100 w-full max-w-lg shadow-xl">
            <div class="card-body">
                <h1 class="card-title text-center text-3xl font-bold">
                    ルーム作成
                </h1>

                <form @submit.prevent="createRoom" class="mt-6 space-y-6">
                    <!-- ボード幅 -->
                    <div class="w-full max-w-xs">
                        <label class="label w-full text-center">
                            <span class="m-auto font-medium"
                                >ボード幅: {{ boardWidth }}
                            </span>
                        </label>
                        <input
                            type="range"
                            min="10"
                            max="25"
                            step="5"
                            class="range w-full"
                            v-model="boardWidth"
                        />
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>|</span>
                            <span>|</span>
                            <span>|</span>
                            <span>|</span>
                        </div>
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>10</span>
                            <span>15</span>
                            <span>20</span>
                            <span>25</span>
                        </div>
                    </div>

                    <!-- ボード高さ -->
                    <div class="form-control">
                        <label class="label w-full">
                            <span class="m-auto font-medium"
                                >ボード高さ: {{ boardHeight }}</span
                            >
                        </label>
                        <input
                            type="range"
                            min="10"
                            max="25"
                            step="5"
                            class="range w-full"
                            v-model="boardHeight"
                        />
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>|</span>
                            <span>|</span>
                            <span>|</span>
                            <span>|</span>
                        </div>
                        <div class="mt-2 flex justify-between px-2.5 text-xs">
                            <span>10</span>
                            <span>15</span>
                            <span>20</span>
                            <span>25</span>
                        </div>
                    </div>

                    <!-- 地雷の割合 -->
                    <div class="form-control">
                        <label class="label w-full">
                            <span class="label-text m-auto font-medium"
                                >地雷の割合 (%)</span
                            >
                        </label>
                        <input
                            type="number"
                            min="5"
                            max="30"
                            class="input input-bordered w-full rounded-md border bg-gray-600"
                            v-model="mineRatio"
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
                            class="select select-bordered w-full rounded-md border bg-gray-600"
                            v-model="roomDuration"
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
                                >ルーム参加上限人数</span
                            >
                        </label>
                        <input
                            type="number"
                            min="2"
                            max="6"
                            class="input input-bordered w-full rounded-md border bg-gray-600"
                            v-model="maxPlayers"
                            required
                        />
                    </div>

                    <div class="form-control w-full">
                        <span
                            class="label-text block w-full text-center font-medium"
                            >ルーム公開設定</span
                        >
                        <div class="my-3 w-full text-center">
                            <span
                                :class="[
                                    'mx-3 text-base font-bold',
                                    roomPermission ? 'text-slate-700' : '',
                                ]"
                                >公開</span
                            >
                            <input
                                type="checkbox"
                                v-model="roomPermission"
                                class="toggle toggle-l m-auto"
                            />
                            <span
                                :class="[
                                    'mx-3 text-base font-bold',
                                    roomPermission ? '' : 'text-slate-700',
                                ]"
                                >非公開</span
                            >
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <button type="submit" class="btn btn-outline mx-auto">
                            ルームを作成する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
