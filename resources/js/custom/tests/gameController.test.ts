import { beforeEach, describe, expect, it } from 'vitest';
import { BoardController, GameController, Tile } from '../useMineSweaper';

describe('GameControllerのテスト', () => {
    let gameController: GameController;
    let boardController: BoardController;

    beforeEach(() => {
        boardController = new BoardController(10, 10, 10);
        gameController = new GameController(boardController);
    });

    it('正常系：ゲーム開始時に指定数地雷をセット', () => {
        const clickTile = boardController.getTile(0, 0);
        if (clickTile === undefined) return;
        gameController.startGame(clickTile);

        let ckeckMineNum = 0;
        boardController.board.getBoard().forEach((widthArr) => {
            widthArr.forEach((tile) => {
                if (tile.isMine) {
                    ckeckMineNum++;
                }
            });
        });
        expect(ckeckMineNum).toBe(10);
    });

    it('ゲームオーバーしない場合', () => {
        const tile = boardController.getTile(0, 0);
        if (tile === undefined) return;
        expect(gameController.isGameOver(tile)).toBe(false);
    });

    it('ゲームオーバーする場合', () => {
        const tile = boardController.getTile(0, 0);
        if (tile === undefined) return;
        tile.isMine = true;
        expect(gameController.isGameOver(tile)).toBe(true);
    });

    it('ゲームクリアする場合', () => {
        gameController.startGame(boardController.getTile(5, 5)!);
        // 地雷以外のタイルを全て開く
        boardController.board.getBoard().forEach((widthArr: Tile[]) => {
            widthArr.forEach((tile) => {
                if (tile.isMine) return;
                boardController.openTile(tile);
            });
        });
        expect(gameController.isGameClear()).toBe(true);
    });

    it('ゲームクリアしない場合', () => {
        expect(gameController.isGameClear()).toBe(false);
    });

    it('シリアライズとデシリアライズのテスト', () => {
        const width = 10;
        const height = 10;
        const numOfMines = 10;

        // 一部のタイルを訪問済みに設定
        boardController.visitedTiles.add('0,0');
        boardController.visitedTiles.add('1,1');

        // シリアライズ
        const serializedData = gameController.serialize();

        // デシリアライズ
        const deserializedGameController =
            GameController.deserialize(serializedData);

        // 元のインスタンスとデシリアライズされたインスタンスが同じ状態であることを確認
        const deserializedBoardController =
            deserializedGameController.boardController;
        expect(deserializedBoardController.width).toBe(width);
        expect(deserializedBoardController.height).toBe(height);
        expect(deserializedBoardController.numOfMines).toBe(numOfMines);
        expect(deserializedBoardController.visitedTiles).toEqual(
            boardController.visitedTiles,
        );
    });
});
