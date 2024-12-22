import { beforeEach, describe, expect, it } from 'vitest';
import { useMineSweaper } from '../useMineSweaper';

describe('useMineSweaperのテスト', () => {
    let boardController: ReturnType<typeof useMineSweaper>['boardController'];
    let gameController: ReturnType<typeof useMineSweaper>['gameController'];
    let OpenTileList: ReturnType<typeof useMineSweaper>['OpenTileList'];
    let startGame: ReturnType<typeof useMineSweaper>['startGame'];
    let reInstance: ReturnType<typeof useMineSweaper>['reInstance'];

    beforeEach(() => {
        const mineSweaper = useMineSweaper(10, 10, 10);
        boardController = mineSweaper.boardController;
        gameController = mineSweaper.gameController;
        OpenTileList = mineSweaper.OpenTileList;
        startGame = mineSweaper.startGame;
        reInstance = mineSweaper.reInstance;
    });

    it('初期化テスト', () => {
        // 初期化時のボードサイズを確認
        expect(boardController.value.width).toBe(10);
        expect(boardController.value.height).toBe(10);

        // 初期化時の地雷数を確認
        expect(boardController.value.numOfMines).toBe(10);

        // 初期化時のOpenTileListが空であることを確認
        expect(OpenTileList.value.size).toBe(0);
    });

    it('ゲーム開始テスト', () => {
        const clickTile = boardController.value.board.getBoard()[5][5];

        // ゲームを開始
        startGame(clickTile);

        // ゲーム開始後にOpenTileListが更新されていることを確認
        expect(OpenTileList.value.size).toBeGreaterThan(0);

        // クリックしたタイルが展開されていることを確認
        expect(clickTile.isOpen).toBe(true);

        // 地雷がセットされていることを確認
        let mineCount = 0;
        boardController.value.board.getBoard().forEach((widthArr) => {
            widthArr.forEach((tile) => {
                if (tile.isMine) {
                    mineCount++;
                }
            });
        });

        expect(mineCount).toBe(10);
    });

    it('再インスタンス化テスト', () => {
        // 再インスタンス化前にゲームを開始
        const clickTile = boardController.value.board.getBoard()[5][5];
        // ゲームを開始。ボードに変更あり
        startGame(clickTile);

        //  再インスタンス化
        reInstance();

        // 再インスタンス化後にボードがリセットされていることを確認
        expect(boardController.value.width).toBe(10);
        expect(boardController.value.height).toBe(10);
        expect(boardController.value.numOfMines).toBe(10);
        expect(OpenTileList.value.size).toBe(0);
        // 地雷がセットされていないことを確認
        let mineCount = 0;
        boardController.value.board.getBoard().forEach((widthArr) => {
            widthArr.forEach((tile) => {
                if (tile.isMine) {
                    mineCount++;
                }
            });
        });

        expect(mineCount).toBe(0);
    });
});
