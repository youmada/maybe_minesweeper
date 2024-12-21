import { describe, expect, it } from 'vitest';
import { Board } from '../useMineSweaper';

describe('ボードクラスのテスト', () => {
    it('ボードクラス初期化テスト', () => {
        const board = new Board(3, 3);
        expect(board.width).toBe(3);
        expect(board.height).toBe(3);
        let index = 0;
        board.getBoard().forEach((widthArr) => {
            widthArr.forEach((tile) => {
                index++;
            });
        });
        expect(index).toBe(9);
    });

    it('ボードの初期状態の確認', () => {
        const board = new Board(3, 3);
        board.getBoard().forEach((widthArr, xIndex) => {
            widthArr.forEach((tile, yIndex) => {
                expect(tile).toEqual({
                    _x: xIndex,
                    _y: yIndex,
                    _isMine: false,
                    _isOpen: false,
                    _isFlag: false,
                });
            });
        });
    });

    it('ボードサイズが0の場合の動作確認', () => {
        const board = new Board(0, 0);
        expect(board.width).toBe(0);
        expect(board.height).toBe(0);
        expect(board.getBoard().length).toBe(0);
    });
});
