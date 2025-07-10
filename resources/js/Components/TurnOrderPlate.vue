<script setup lang="ts">
import TurnOrderPlayerTag from '@/Components/TurnOrderPlayerTag.vue';

type Player = {
    sessionId: string;
    isCurrentTurn: boolean;
};
defineProps<{
    players: Player[];
}>();
</script>
<template>
    <div>
        <ul class="min-h-96 rounded-lg border-4 border-gray-400">
            <li class="m-5">
                <TurnOrderPlayerTag
                    v-for="(player, index) in players"
                    :key="player.sessionId"
                    :name="`プレイヤー${index + 1}`"
                    :isCurrentTurn="player.isCurrentTurn"
                ></TurnOrderPlayerTag>
            </li>
        </ul>
    </div>
</template>
