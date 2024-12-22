import { describe, expect, it } from 'vitest';
import { BoardController, Tile } from '../useMineSweaper';

describe('ボードコントローラクラスのテスト', () => {
    it('ボードコントローラクラス初期化テスト', () => {
        // 幅10、高さ10、地雷数10のボードコントローラクラスを生成
        const boardController = new BoardController(10, 10, 10);
        expect(boardController.width).toBe(10);
        expect(boardController.height).toBe(10);
        expect(boardController.numOfMines).toBe(10);
        expect(boardController.visitedTiles.size).toBe(0);
    });

    // getAroundTilesメソッドは引数で渡されたタイルの周囲8マスのタイルを返す。
    it('getAroundTilesメソッドのテスト', () => {
        const boardController = new BoardController(10, 10, 10);
        const tile = boardController.board.getBoard()[5][5];
        const aroundTiles = boardController.getAroundTiles(tile);
        expect(aroundTiles.length).toBe(8);
        // 時計回りに周囲8マスの座標を設定
        const aroundPositionDiff = [
            { x: 0, y: -1 },
            { x: 1, y: -1 },
            { x: 1, y: 0 },
            { x: 1, y: 1 },
            { x: 0, y: 1 },
            { x: -1, y: 1 },
            { x: -1, y: 0 },
            { x: -1, y: -1 },
        ];
        aroundTiles.forEach((aroundTile, index) => {
            expect(aroundTile.x).toBe(tile.x + aroundPositionDiff[index].x);
            expect(aroundTile.y).toBe(tile.y + aroundPositionDiff[index].y);
        });
    });

    // setMinesメソッドはnumOfMinesの数だけ地雷を設定する。また、必ずクリックしたタイルの周囲に地雷が存在しないようにする。
    // setMinesメソッドは内部でgetRamdomTileメソッドを使用しているので、getRamdomTileメソッドのテストも兼ねている。
    it('正常系：setMinesテスト', () => {
        const boardController = new BoardController(10, 10, 10);
        // クリックしたタイルの座標を設定
        const clickTile = boardController.board.getBoard()[5][5];
        boardController.setMines(clickTile);
        // numOfMinesの数だけ地雷が設定されているか確認
        let mineCount = 0;
        boardController.board.getBoard().forEach((widthArr) => {
            widthArr.forEach((tile) => {
                if (tile.isMine) {
                    mineCount++;
                }
            });
        });
        expect(mineCount).toBe(10);
        // クリックしたタイルに地雷が存在しないか確認
        const clickTilePosition = clickTile;
        expect(clickTilePosition.isMine).toBe(false);

        // クリックしたタイルの周囲8マスに地雷が存在しないか確認
        const aroundTiles = boardController.getAroundTiles(clickTile);
        aroundTiles.forEach((aroundTile) => {
            expect(aroundTile.isMine).toBe(false);
        });
    });

    it('正常系：checkAroundMinesテスト', () => {
        const boardController = new BoardController(10, 10, 10);
        const clickTile = boardController.board.getBoard()[5][5];
        // 上と下に地雷を設定
        const setAroundMines = [
            { x: 5, y: 4 },
            { x: 5, y: 6 },
        ];
        // 地雷を設定
        setAroundMines.forEach((mine) => {
            boardController.board.getBoard()[mine.x][mine.y].isMine = true;
        });
        // clickTileの周囲8マスの地雷数を取得
        boardController.checkAroundMines(clickTile);
        // クリックしたタイルの周囲8マスの地雷数が正しく設定されているか確認
        const aroundTiles = boardController.getAroundTiles(clickTile);
        let aroundMinesCount = 0;
        aroundTiles.forEach((aroundTile) => {
            if (aroundTile.isMine) {
                aroundMinesCount++;
            }
        });
        // 設定した地雷数と取得した地雷数が一致するか確認
        expect(aroundMinesCount).toBe(setAroundMines.length);
    });

    // 正常系：ボードコントローラクラスのタイルにフラグを立てる処理
    it('正常系：ボードコントローラクラスのタイルにフラグを立てる処理', () => {
        const boardController = new BoardController(10, 10, 10);
        const clickTile = boardController.board.getBoard()[5][5];
        boardController.toggleFlag(clickTile);
        expect(clickTile.isFlag).toBe(true);
    });

    it('異常系：ボードコントローラクラスのタイルにフラグを立てる処理', () => {
        const boardController = new BoardController(10, 10, 10);
        const noExistTile = new Tile(100, 100);
        // タイルが存在しない場合は早期リターン
        const result = boardController.toggleFlag(noExistTile);
        expect(result).toBe(undefined);
    });

    it('正常系:ボードのタイル展開処理', () => {
        const boardController = new BoardController(10, 10, 10);
        const clickTile = boardController.board.getBoard()[5][5];

        // 特定の位置に地雷を設定
        const mines = [
            { x: 4, y: 4 },
            { x: 4, y: 5 },
            { x: 4, y: 6 },
            { x: 5, y: 4 },
            { x: 5, y: 6 },
            { x: 6, y: 4 },
            { x: 6, y: 5 },
            { x: 6, y: 6 },
        ];
        mines.forEach((mine) => {
            boardController.board.getBoard()[mine.x][mine.y].isMine = true;
        });

        // タイルを展開
        boardController.openTile(clickTile);

        // 展開されたタイルの数を確認
        expect(boardController.visitedTiles.size).toBe(1);

        // 周囲のタイルが展開されていないことを確認
        mines.forEach((mine) => {
            expect(
                boardController.board.getBoard()[mine.x][mine.y].isOpen,
            ).toBe(false);
        });
    });

    it('正常系: 再帰的なタイル展開処理', () => {
        const boardController = new BoardController(10, 10, 0); // 地雷を0に設定
        const clickTile = boardController.board.getBoard()[5][5];

        /*
        想定される10x10のボード
        2はクリックしたタイルを表す
        1は地雷を表す
        0は地雷でないことを表す
        [0,0,0,0,0,0,0,0,0,0]
        [0,1,0,0,0,0,0,0,1,0]
        [0,0,0,0,0,0,0,0,0,0]
        [0,0,0,0,0,0,0,0,0,0]
        [0,0,0,0,2,0,0,0,0,0]
        [0,0,0,0,0,0,0,0,0,0]
        [0,0,0,0,0,0,0,0,0,0]
        [0,1,0,0,0,0,0,0,1,0]
        [0,0,0,0,0,0,0,0,0,0]
        */

        // 設置する地雷は10x10のボードの四隅に設定
        const mines = [
            { x: 1, y: 1 },
            { x: 1, y: 8 },
            { x: 8, y: 1 },
            { x: 8, y: 8 },
        ];
        mines.forEach((mine) => {
            boardController.board.getBoard()[mine.x][mine.y].isMine = true;
        });

        // ボードの状態を視覚的に表示する関数
        const printBoard = (board: Tile[][]) => {
            const boardString = board
                .map((row) =>
                    row
                        .map((tile) =>
                            tile.isMine ? 'M' : tile.isOpen ? 'O' : 'X',
                        )
                        .join(' '),
                )
                .join('\n');
            console.log(boardString);
            console.log('-----------------');
        };

        // ボードの状態を表示
        printBoard(boardController.board.getBoard());

        // タイルを展開
        boardController.openTile(clickTile);

        // 4つの地雷を四隅から1マス離した部分に設定しているので、その周囲を省いて、10x10のボードの展開されたタイルの数は10x10 - 4x4 = 84
        // 8x4ではないのは、周囲に地雷が存在する場合は展開されないが、展開したタイルの周囲に地雷がある場合は、展開されるため。
        const expectedOpenTiles = 10 * 10 - 4 * 4;

        printBoard(boardController.board.getBoard());

        // 展開されたタイルの数を確認
        expect(boardController.visitedTiles.size).toBe(expectedOpenTiles);

        // 周囲のタイルが展開されていることを確認
        expect(boardController.board.getBoard()[4][4].isOpen).toBe(true);
        expect(boardController.board.getBoard()[6][6].isOpen).toBe(true);
    });

    it('異常系：openTileに存在しないタイルを渡す', () => {
        const boardController = new BoardController(10, 10, 10);
        const noExistTile = new Tile(100, 100);
        // タイルが存在しない場合は早期リターン
        const result = boardController.openTile(noExistTile);
        expect(result).toBe(undefined);
    });
});
