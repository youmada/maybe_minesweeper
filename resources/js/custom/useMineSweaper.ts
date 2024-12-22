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
    private _numOfMine: number;
    board: Board;
    visitedTiles: Set<string>;

    constructor(width: number, height: number, numOfMine: number) {
        this._numOfMine = numOfMine;
        this.board = new Board(width, height);
        this.visitedTiles = new Set();
    }

    get width() {
        return this.board.width;
    }

    get height() {
        return this.board.height;
    }

    get numOfMines() {
        return this._numOfMine;
    }

    setMines(clickTilePos: Tile): void {
        let iterator = this.numOfMines;
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
        const randomHeigth = Math.floor(Math.random() * this.height);
        return this.board.getBoard()[randomWidth][randomHeigth];
    }

    getTile(x: number, y: number): Tile | undefined {
        if (x < 0 || x > this.width - 1) return;
        if (y < 0 || y > this.height - 1) return;
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
        // 時計回りで周囲8マスの座標を設定
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

    private chainOpenTile(diffX: number, diffY: number, tile: Tile) {
        // 1.現在位置を把握
        const currTile = this.getTile(tile.x + diffX, tile.y + diffY);
        if (!currTile) return;

        const tileKey = `${currTile.x},${currTile.y}`;

        // すでにタイルが空いているなら早期リターン
        if (this.visitedTiles.has(tileKey)) return;
        // タイルが空いていないので、訪れたタイルに追加
        this.visitedTiles.add(tileKey);

        // フラグが立っている場合は、フラグを外す
        currTile.openTile();
        currTile.isFlag = false;

        // 2.現在位置の周辺地雷数を取得
        const aroundMines = this.checkAroundMines(currTile);

        // 3.八方向を確認して、それぞれの方向の隣接タイルの周辺地雷数が0の場合に、タイルを展開
        if (aroundMines !== 0) return;

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
        // タイル展開は全てchainOpenTileで行う
        this.chainOpenTile(0, 0, tile);
    }

    toggleFlag(tile: Tile) {
        // タイルが存在しない場合は早期リターン
        if (this.getTile(tile.x, tile.y) === undefined) return;
        tile.toggleFlag();
    }
}

export class Board {
    private _width: number;
    private _height: number;
    private board: Tile[][];

    constructor(width: number, height: number) {
        this._width = width;
        this._height = height;
        this.board = this.createBoard();
    }

    private createBoard(): Tile[][] {
        const board: Tile[][] = [];
        for (let i = 0; i < this._width; i++) {
            const widthArr = [];
            for (let j = 0; j < this._height; j++) {
                const title = new Tile(i, j);
                widthArr.push(title);
            }
            board.push(widthArr);
        }
        return board;
    }

    get width() {
        return this._width;
    }
    get height() {
        return this._height;
    }

    getBoard() {
        return this.board;
    }
}

export class GameController {
    private boardController: BoardController;
    constructor(boardController: BoardController) {
        this.boardController = boardController;
    }

    isGameOver(tile: Tile) {
        if (tile.isMine) return true;
        return false;
    }

    isGameClear() {
        const board = this.boardController;
        const restClosedTiles =
            board.width * board.height -
            board.numOfMines -
            this.boardController.visitedTiles.size;

        if (restClosedTiles === 0) return true;
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
    const OpenTileList = ref(boardController.value.visitedTiles);

    function reInstance() {
        boardController.value = new BoardController(
            boardWidth,
            boardHeight,
            numOfMine,
        );
        OpenTileList.value = boardController.value.visitedTiles;
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
        OpenTileList,
        reInstance,
        startGame,
    };
}
