import { Board } from '@/custom/domain/mineSweeper';
import { defineStore } from 'pinia';

interface saveData {
    board: Board;
    numOfMines: number;
    arrayToVisitedTiles: string[];
    previousGameMode: string; // コンティニュー時、ゲームオーバーから再度ゲームを始める際に、前回のゲームモードを保持するため
}

interface State {
    saveData: saveData | null;
}

export const useSaveDataStore = defineStore('saveData', {
    state: (): State => ({
        saveData: null,
    }),

    getters: {
        getSaveData: (state) => {
            return state.saveData;
        },
    },
    actions: {
        loadSaveData() {
            const saveData = localStorage.getItem('saveData');
            if (saveData) {
                this.saveData = JSON.parse(saveData);
            }
        },

        setSaveData(
            data: Board,
            numOfMines: number,
            visitedTiles: Set<string>,
            previousGameMode: string,
        ) {
            const arrayToVisitedTiles = Array.from(visitedTiles);
            const saveData = {
                board: data,
                numOfMines,
                arrayToVisitedTiles,
                previousGameMode,
            };
            this.saveData = saveData;
            localStorage.setItem('saveData', JSON.stringify(saveData));
        },
    },
});
