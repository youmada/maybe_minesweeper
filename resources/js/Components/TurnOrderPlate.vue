<script setup lang="ts">
import TurnOrderPlayerTag from '@/Components/TurnOrderPlayerTag.vue';

type Player = {
    id: string;
    joinedAt: string;
    isCurrentTurn: boolean;
    isOwn?: boolean;
};
defineProps<{
    players: Player[];
}>();
</script>
<template>
    <div>
        <ul class="min-h-96 rounded-lg border-4 border-gray-400">
            <li class="flex flex-col justify-center">
                <TurnOrderPlayerTag
                    v-for="(player, index) in players"
                    :key="player.id"
                    :name="player.isOwn ? 'あなた' : `プレイヤー${index + 1}`"
                    :isCurrentTurn="player.isCurrentTurn"
                ></TurnOrderPlayerTag>
            </li>
        </ul>
    </div>
</template>
