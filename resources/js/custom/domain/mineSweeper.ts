// domain/minesweeper.ts

// タイル1個の情報
export interface Tile {
    x: number;
    y: number;
    isMine: boolean;
    isOpen: boolean;
    isFlag: boolean;
}

// ボード全体
export type Board = Tile[][];

/**
 * ボードを作成する (幅、高さを受け取って2次元配列を返す)
 */
export function createBoard(width: number, height: number): Board {
    const board: Board = [];
    for (let y = 0; y < height; y++) {
        const row: Tile[] = [];
        for (let x = 0; x < width; x++) {
            row.push({
                x,
                y,
                isMine: false,
                isOpen: false,
                isFlag: false,
            });
        }
        board.push(row);
    }
    return board;
}

/**
 * ランダムに地雷を配置する
 * 初クリック位置とその周囲1マスには地雷を置かない
 */
export function setMines(
    board: Board,
    numOfMine: number,
    firstClick: { x: number; y: number },
) {
    const height = board.length;
    const width = board[0]?.length ?? 0;

    if (
        firstClick.x < 0 ||
        firstClick.x >= width ||
        firstClick.y < 0 ||
        firstClick.y >= height
    ) {
        throw new Error('初クリック位置が不正です');
    }

    if (numOfMine >= height * width) {
        throw new Error('地雷数が多すぎます');
    }

    // 初クリック周囲のタイルを避ける
    const excludeTiles = new Set<string>();
    excludeTiles.add(`${firstClick.x},${firstClick.y}`);
    getAroundTiles(board, firstClick.x, firstClick.y).forEach((tile) => {
        excludeTiles.add(`${tile.x},${tile.y}`);
    });

    let minesToPlace = numOfMine;
    while (minesToPlace > 0) {
        const rx = Math.floor(Math.random() * width);
        const ry = Math.floor(Math.random() * height);
        const key = `${rx},${ry}`;
        if (excludeTiles.has(key)) continue; // 置けない場所
        const tile = board[ry][rx];
        if (tile.isMine) continue; // すでに地雷がある
        tile.isMine = true;
        excludeTiles.add(key);
        minesToPlace--;
    }
}

/**
 * 周囲8マスのTileをまとめて返す
 */
export function getAroundTiles(board: Board, x: number, y: number): Tile[] {
    // 時計回りに周囲8マスの座標を定義
    const directions = [
        { dx: 0, dy: -1 },
        { dx: 1, dy: -1 },
        { dx: 1, dy: 0 },
        { dx: 1, dy: 1 },
        { dx: 0, dy: 1 },
        { dx: -1, dy: 1 },
        { dx: -1, dy: 0 },
        { dx: -1, dy: -1 },
    ];
    const result: Tile[] = [];
    directions.forEach(({ dx, dy }) => {
        const nx = x + dx;
        const ny = y + dy;
        if (ny >= 0 && ny < board.length && nx >= 0 && nx < board[0].length) {
            result.push(board[ny][nx]);
        }
    });
    return result;
}

/**
 * タイルを開く (再帰展開も行う)
 */
export function openTile(board: Board, tile: Tile, visited: Set<string>) {
    const tileKey = `${tile.x},${tile.y}`;
    if (visited.has(tileKey)) return;
    // タイルを「訪問・開く」
    tile.isOpen = true;
    tile.isFlag = false;
    visited.add(tileKey);

    // 周囲の地雷数を確認
    const around = getAroundTiles(board, tile.x, tile.y);
    const aroundMines = around.filter((t) => t.isMine).length;

    // 周囲地雷数が0なら、八方向のタイルも再帰的に開く
    if (aroundMines === 0) {
        around.forEach((t) => {
            openTile(board, t, visited);
        });
    }
}

/**
 * フラグを立て外しする
 */
export function toggleFlag(tile: Tile) {
    if (tile.isOpen) return; // 開いてるタイルはフラグ不可
    tile.isFlag = !tile.isFlag;
}

/**
 * ゲームオーバー判定 (tileを開いた瞬間)
 */
export function checkGameOver(tile: Tile) {
    return tile.isMine && tile.isOpen;
}

/**
 * クリア判定 (残りの閉じタイルが地雷の数だけになったとき)
 */
export function checkGameClear(board: Board, totalMines: number) {
    let closedTiles = 0;
    for (const row of board) {
        for (const tile of row) {
            if (!tile.isOpen) {
                closedTiles++;
            }
        }
    }
    return closedTiles === totalMines;
}
