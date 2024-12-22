import { describe, expect, it } from 'vitest';
import { Tile } from '../useMineSweaper';

describe('タイルクラスのテスト', () => {
    it('タイルクラス初期化テスト', () => {
        const tile = new Tile(1, 2);
        expect(tile.x).toBe(1);
        expect(tile.y).toBe(2);
        expect(tile.isMine).toBe(false);
        expect(tile.isOpen).toBe(false);
        expect(tile.isFlag).toBe(false);
    });

    it('正常系：タイルフラグのトグル処理', () => {
        const tile = new Tile(1, 2);
        expect(tile.isFlag).toBe(false);
        tile.toggleFlag();
        expect(tile.isFlag).toBe(true);
        tile.toggleFlag();
        expect(tile.isFlag).toBe(false);
    });

    it('正常系：タイルが開く', () => {
        const tile = new Tile(1, 2);
        expect(tile.isOpen).toBe(false);
        tile.openTile();
        expect(tile.isOpen).toBe(true);
    });

    it('正常系：タイルに地雷セット', () => {
        const tile = new Tile(1, 2);
        expect(tile.isMine).toBe(false);
        tile.isMine = true;
        expect(tile.isMine).toBe(true);
    });

    it('正常系：タイルポジションを取得', () => {
        const tile = new Tile(1, 2);
        const position = tile.getPosition();
        expect(position).toEqual({ x: 1, y: 2 });
    });
});
