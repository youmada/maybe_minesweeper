import { ref } from "vue";

export class Tile {
  x: number;
  y: number;
  isMine: boolean;
  isOpen: boolean;
  isFlag: boolean;
  constructor(x: number, y: number) {
    this.x = x;
    this.y = y;
    this.isMine = false;
    this.isOpen = false;
    this.isFlag = false;
  }

  getPosition() {
    return { x: this.x, y: this.y };
  }

  toggleFlag() {
    this.isFlag = !this.isFlag;
  }

  getFlagState() {
    return this.isFlag;
  }

  setMine() {
    this.isMine = true;
  }

  openTile() {
    this.isOpen = true;
  }

  getTileState() {
    return { isMine: this.isMine, isOpen: this.isOpen };
  }

  checkAroundMines(board: IBoard) {
    let numOfMine = 0;
    // 現在位置
    const tiles = this.getAroundTiles(board);

    tiles.forEach((tileData) => {
      if (tileData.getTileState().isMine) numOfMine++;
    });
    return numOfMine;
  }

  getAroundTiles(board: IBoard) {
    const currPos = this.getPosition();
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
      const tile = board.getTile(tileData.x, tileData.y);
      if (tile !== undefined) aroundTIles.push(tile);
    });

    return aroundTIles;
  }
}

export interface IBoard {
  width: number;
  heigth: number;
  numOfMine: number;
  board: Tile[][];
  visitedTiles: Set<string>;
  numOfDeployableTiles: number;
  getBoard(): Tile[][];
  getTile(x: number, y: number): Tile | undefined;
  setMines(board: Tile[][], clickTilePos: Tile): void;
}

export class Board {
  width: number;
  heigth: number;
  numOfMine: number;
  board: Tile[][];
  visitedTiles: Set<string>;
  numOfDeployableTiles: number;

  constructor(width: number, height: number, numOfMine: number) {
    this.width = width;
    this.heigth = height;
    this.numOfMine = numOfMine;
    this.board = this.createBoard();
    this.visitedTiles = new Set();
    this.numOfDeployableTiles = this.width * this.heigth - this.numOfMine;
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

  setMines(board: Tile[][], clickTilePos: Tile): void {
    let iterator = this.numOfMine;
    const alreadySetMines = new Set();
    alreadySetMines.add(clickTilePos);
    const aroundTiles = clickTilePos.getAroundTiles(this);
    // 最初のクリックタイルと周辺8マスは初期値で開けておく。
    clickTilePos.openTile();
    aroundTiles.map((tile) => tile.openTile());
    aroundTiles.forEach((tile) => alreadySetMines.add(tile));

    //wileループに入る前に、最初に押したタイルとその周囲1マスのタイルが入っている。
    while (iterator > 0) {
      const currentTile = this.getRamdomTile(board);
      if (!currentTile) continue;
      const isalready = alreadySetMines.has(currentTile);
      if (isalready) continue;
      alreadySetMines.add(currentTile);
      currentTile.setMine();
      iterator--;
    }
  }

  private getRamdomTile(board: Tile[][]) {
    if (this.board === null) return;
    const randomWidth = Math.floor(Math.random() * this.width);
    const randomHeigth = Math.floor(Math.random() * this.heigth);
    return board[randomWidth][randomHeigth];
  }

  getBoard(): Tile[][] {
    return this.board;
  }

  getTile(x: number, y: number): Tile | undefined {
    if (x < 0 || x > this.width - 1) return;
    if (y < 0 || y > this.heigth - 1) return;
    return this.board[x][y];
  }
}

export function useMineSweeper(boardWidth: number, boardHeight: number, numOfMine: number) {
  const board = ref(new Board(boardWidth, boardHeight, numOfMine));
  const tiles = ref(board.value.getBoard());
  const numOfOpenTiles = ref(board.value.visitedTiles);

  function reInstance() {
    board.value = new Board(boardWidth, boardHeight, numOfMine);
    tiles.value = board.value.getBoard();
    numOfOpenTiles.value = board.value.visitedTiles;
  }

  function startGame(clickTilePos: Tile) {
    board.value.setMines(board.value.getBoard(), clickTilePos);
  }

  return {
    board,
    tiles,
    numOfOpenTiles,
    reInstance,
    startGame,
  };
}
