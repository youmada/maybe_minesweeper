import { ref } from 'vue';

export class Tile {
    private _x: number;
    private _y: number;
    private _isMine: boolean;
    private _isOpen: boolean;
    private _isFlag: boolean;
    constructor(x: number, y: number) {
        this._x = x;
        this._y = y;
        this._isMine = false;
        this._isOpen = false;
        this._isFlag = false;
    }

    getPosition() {
        return { x: this._x, y: this._y };
    }

    toggleFlag() {
        this._isFlag = !this._isFlag;
    }

    openTile() {
        this._isOpen = true;
    }

    get x() {
        return this._x;
    }

    get y() {
        return this._y;
    }

    get isOpen() {
        return this._isOpen;
    }

    get isMine() {
        return this._isMine;
    }

    set isMine(isMine: boolean) {
        this._isMine = isMine;
    }

    get isFlag() {
        return this._isFlag;
    }

    set isFlag(isFlag: boolean) {
        this._isFlag = isFlag;
    }
}

export class BoardController {
    private _width: number;
    private _heigth: number;
    private _numOfMine: number;
    board: Board;
    visitedTiles: Set<string>;
    numOfDeployableTiles: number;

    constructor(width: number, height: number, numOfMine: number) {
        this._width = width;
        this._heigth = height;
        this._numOfMine = numOfMine;
        this.board = new Board(width, height);
        this.visitedTiles = new Set();
        this.numOfDeployableTiles = this.width * this.heigth - this.numOfMine;
    }

    get width() {
        return this._width;
    }

    get heigth() {
        return this._heigth;
    }

    get numOfMine() {
        return this._numOfMine;
    }

    getBoard() {
        return this.board.getBoard();
    }

    setMines(clickTilePos: Tile): void {
        let iterator = this.numOfMine;
        const alreadySetMines = new Set<Tile>();
        alreadySetMines.add(clickTilePos);
        // 周辺8マスを取得
        const aroundTiles = this.getAroundTiles(clickTilePos);
        // 最初のクリックタイルと周辺8マスは初期値で開けておく。
        clickTilePos.openTile();
        // すでに設置されているマインを開ける
        aroundTiles.map((tile) => tile.openTile());
        // すでに設置されているマインをセット
        aroundTiles.forEach((tile) => alreadySetMines.add(tile));

        //wileループに入る前に、最初に押したタイルとその周囲1マスのタイルが入っている。
        while (iterator > 0) {
            const currentTile = this.getRamdomTile();
            if (!currentTile) continue;
            const isalready = alreadySetMines.has(currentTile);
            if (isalready) continue;
            alreadySetMines.add(currentTile);
            currentTile.isMine = true;
            iterator--;
        }
    }

    private getRamdomTile() {
        if (this.board === null) return;
        const randomWidth = Math.floor(Math.random() * this.width);
        const randomHeigth = Math.floor(Math.random() * this.heigth);
        return this.board.getBoard()[randomWidth][randomHeigth];
    }

    getTile(x: number, y: number): Tile | undefined {
        if (x < 0 || x > this.width - 1) return;
        if (y < 0 || y > this.heigth - 1) return;
        return this.board.getBoard()[x][y];
    }

    checkAroundMines(currentTile: Tile): number {
        let numOfMine = 0;
        // 現在位置
        const tiles = this.getAroundTiles(currentTile);

        tiles.forEach((tileData) => {
            if (tileData.isMine) numOfMine++;
        });
        return numOfMine;
    }

    getAroundTiles(currentTile: Tile): Tile[] {
        const currPos = currentTile.getPosition();
        const tiles: { x: number; y: number }[] = [
            // 現在位置から真上
            { x: currPos.x, y: currPos.y - 1 },
            // 現在位置から右上
            { x: currPos.x + 1, y: currPos.y - 1 },
            // 現在位置から右
            { x: currPos.x + 1, y: currPos.y },
            // 現在位置から右下
            { x: currPos.x + 1, y: currPos.y + 1 },
            // 現在位置から真下
            { x: currPos.x, y: currPos.y + 1 },
            // 現在位置から左下
            { x: currPos.x - 1, y: currPos.y + 1 },
            // 現在位置から左
            { x: currPos.x - 1, y: currPos.y },
            // 現在位置から左上
            { x: currPos.x - 1, y: currPos.y - 1 },
        ];

        const aroundTIles: Tile[] = [];

        tiles.forEach((tileData) => {
            const tile = this.getTile(tileData.x, tileData.y);
            if (tile !== undefined) aroundTIles.push(tile);
        });

        return aroundTIles;
    }

    chainOpenTile(diffX: number, diffY: number, tile: Tile) {
        // 1.現在位置を把握
        const currTile = this.getTile(tile.x + diffX, tile.y + diffY);
        if (!currTile) return;
        // 1.現在位置を把握
        const tileKey = `${currTile.x},${currTile.y}`;

        // すでにタイルが空いているなら早期リターン
        if (this.visitedTiles.has(tileKey)) return;
        this.visitedTiles.add(tileKey);

        const aroundMines = this.checkAroundMines(currTile);
        currTile.openTile();
        currTile.isFlag = false;

        if (aroundMines !== 0) return;

        // 3.八方向を確認して、それぞれの方向の隣接タイルの周辺地雷数が0の場合に、タイルを展開

        // 4.隣接タイルに地雷が隣接するまで、再帰的に実行する。

        const directions = [
            { x: 0, y: -1 }, // 現在位置から真上
            { x: 1, y: -1 }, // 現在位置から右上
            { x: 1, y: 0 }, // 現在位置から右
            { x: 0, y: 1 }, // 現在位置から右下
            { x: 1, y: 1 }, // 現在位置から下
            { x: -1, y: 1 }, // 現在位置から左下
            { x: -1, y: 0 }, // 現在位置から左
            { x: -1, y: -1 }, // 現在位置から左上
        ];
        directions.forEach((direction) => {
            this.chainOpenTile(direction.x, direction.y, currTile);
        });
    }

    openTile(tile: Tile) {
        if (tile === undefined) return;
        tile.openTile();
        // タイルを開けたら、開けたタイルを記録する
        this.visitedTiles.add(`${tile.x},${tile.y}`);
        // タイルを開けたら、展開可能なタイルを減らす
        this.numOfDeployableTiles--;

        // 再帰的にタイルを開けることができる場合、再帰的に開ける
        this.chainOpenTile(0, 0, tile);
    }

    isOpenTile(tile: Tile) {
        if (tile.isFlag) return false;
        if (tile.isOpen) return false;
        return true;
    }
}

class Board {
    private width: number;
    private heigth: number;
    private board: Tile[][];

    constructor(width: number, height: number) {
        this.width = width;
        this.heigth = height;
        this.board = this.createBoard();
    }

    private createBoard(): Tile[][] {
        const board: Tile[][] = [];
        for (let i = 0; i < this.width; i++) {
            const widthArr = [];
            for (let j = 0; j < this.heigth; j++) {
                const title = new Tile(i, j);
                widthArr.push(title);
            }
            board.push(widthArr);
        }
        return board;
    }

    getBoard() {
        return this.board;
    }
}

class GameController {
    private boardController: BoardController;
    constructor(boardController: BoardController) {
        this.boardController = boardController;
    }

    isGameOver(tile: Tile) {
        if (tile.isMine) return true;
        return false;
    }

    isGameClear() {
        if (this.boardController.numOfDeployableTiles === 0) return true;
        return false;
    }

    startGame(clickTile: Tile) {
        this.boardController.setMines(clickTile);
    }
}

export function useMineSweeper(
    boardWidth: number,
    boardHeight: number,
    numOfMine: number,
) {
    const boardController = ref<BoardController>(
        new BoardController(boardWidth, boardHeight, numOfMine),
    );
    const gameController = ref<GameController>(
        new GameController(boardController.value as BoardController),
    );
    const numOfOpenTiles = ref(boardController.value.visitedTiles);

    function reInstance() {
        boardController.value = new BoardController(
            boardWidth,
            boardHeight,
            numOfMine,
        );
        numOfOpenTiles.value = boardController.value.visitedTiles;
        gameController.value = new GameController(
            boardController.value as BoardController,
        );
    }

    function startGame(clickTilePos: Tile) {
        gameController.value.startGame(clickTilePos);
    }

    return {
        boardController,
        gameController,
        numOfOpenTiles,
        reInstance,
        startGame,
    };
}
