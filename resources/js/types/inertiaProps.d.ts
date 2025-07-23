import { Tile } from '@/custom/domain/mineSweeper';

export type RoomData = {
    name: string;
    publicId: string;
    ownerId: string;
    maxPlayer: number;
    magicLink: string;
    status: string;
    currentPlayer: string;
    turnActionState: {
        flagCount: number;
        flagLimit: number;
    };
};
export type GameState = {
    width: number;
    height: number;
    numOfMines: number;
    isGameStarted: boolean;
    isGameOver: boolean;
    isGameClear: boolean;
    tileStates: Array<Array<Tile>>;
    visitedTiles: number;
};

export type Player = {
    id: string;
    joinedAt: string;
    isCurrentTurn: boolean;
    isOwn?: boolean;
};
