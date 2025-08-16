// stores/minesweeperStore.ts
import {
    Board,
    checkGameClear,
    checkGameOver,
    createBoard,
    openTile,
    setMines,
    toggleFlag,
} from '@/custom/domain/mineSweeper';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useGameStore = defineStore('gameStore', {
    // ============================
    // state
    // ============================
    state: () => ({
        // ゲーム盤面: 2次元配列
        board: [] as Board,
        // 一度でも開いたタイルのキー(x,y)
        visitedTiles: new Set<string>(),
        // ゲーム開始フラグ
        isGameStarted: ref(false),
        // ゲームオーバーかどうか
        isGameOver: ref(false),
        // ゲームクリアかどうか
        isGameClear: ref(false),
        isFlagMode: ref(false),
        // ボード幅・高さ・地雷数(固定値の例)
        width: 0,
        height: 0,
        numOfMines: 0,
    }),

    // ============================
    // getters
    // ============================
    getters: {
        // 例：残りの閉じタイル数
        closedTilesCount(state) {
            let count = 0;
            for (const row of state.board) {
                for (const tile of row) {
                    if (!tile.isOpen) count++;
                }
            }
            return count;
        },

        // 例：残りの閉じタイル数(地雷を除く)
        closedTilesCountWithoutMine(state) {
            let count = 0;
            for (const row of state.board) {
                for (const tile of row) {
                    if (!tile.isOpen && !tile.isMine) count++;
                }
            }
            return count;
        },
        // 他に必要であれば getters を追加
    },

    // ============================
    // actions
    // ============================
    actions: {
        // フラグモードの切り替え
        toggleFlagMode() {
            this.isFlagMode = !this.isFlagMode;
        },
        // 新規ゲーム開始(リセット)
        initiaraize(width: number, height: number) {
            this.board = createBoard(width, height);
            this.width = width;
            this.height = height;
            this.visitedTiles = new Set();
            this.isGameStarted = false;
            this.isGameOver = false;
            this.isGameClear = false;
            this.isFlagMode = false;
        },

        // ゲームの続きから開始
        continueGame(
            board: Board,
            arrayToVisitedTiles: string[],
            numOfMines: number,
        ) {
            this.board = board;
            this.width = board[0].length;
            this.height = board.length;
            this.numOfMines = numOfMines;
            this.visitedTiles = new Set(arrayToVisitedTiles);
            this.isGameStarted = true;
            this.isGameOver = false;
            this.isGameClear = false;
            this.isFlagMode = false;
        },

        // 最初のクリックで地雷を配置＆タイルを開く
        startGame(x: number, y: number) {
            if (this.isGameStarted) return;
            this.isGameStarted = true;

            const firstClick = { x, y };
            // 地雷設置
            setMines(this.board, this.numOfMines, firstClick);
            // 最初にクリックしたタイルを開く
            const tile = this.board[y][x];
            openTile(this.board, tile, this.visitedTiles);

            // 即座にゲームオーバー or クリアかチェック
            this.isGameOver = checkGameOver(tile);
            this.isGameClear = checkGameClear(this.board, this.numOfMines);
        },

        handleClickTile(x: number, y: number) {
            if (this.isGameOver || this.isGameClear) return;

            if (!this.isGameStarted) {
                this.startGame(x, y);
            } else if (this.isFlagMode) {
                this.onToggleFlag(x, y);
            } else {
                this.openTileAction(x, y);
            }
        },

        openTileAction(x: number, y: number) {
            const tile = this.board[y][x];
            if (tile.isFlag) return;

            openTile(this.board, tile, this.visitedTiles);

            this.isGameOver = checkGameOver(tile);
            // ゲームオーバー時にすべてのタイルを展開する。
            if (this.isGameOver) {
                openedAllTiles(this.board);
            }
            this.isGameClear = checkGameClear(this.board, this.numOfMines);
        },
        // フラグのトグル
        onToggleFlag(x: number, y: number) {
            if (!this.isFlagMode) return;
            if (this.isGameOver || this.isGameClear) return;
            const tile = this.board[y][x];
            toggleFlag(tile);
        },
    },
});

function openedAllTiles(board: Board) {
    for (const row of board) {
        for (const tile of row) {
            if (!tile.isOpen) tile.isOpen = true;
            if (tile.isFlag) tile.isFlag = false;
        }
    }
    return board;
}
