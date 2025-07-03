<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomIndexResource;
use App\Models\Room;
use App\Services\Minesweeper\MinesweeperService;
use App\Services\Multi\CreateRoomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MultiRoomController extends Controller
{
    const FLAG_LIMIT = 5;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rooms = Room::where('owner_id', $request->session()->get('player_id', 'test'))->get();

        return Inertia::render('Multi/Rooms', ['data' => RoomIndexResource::collection($rooms)->resolve()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Multi/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateRoomService $createRoomService, MinesweeperService $minesweeperService)
    {

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'boardWidth' => ['required', 'integer', 'min:10', 'max:25'],
            'boardHeight' => ['required', 'integer', 'min:10', 'max:25'],
            'mineRatio' => ['required', 'integer', 'min:10', 'max:40'],
            'expireAt' => ['required', 'integer', 'in:1,7,14'],
            'maxPlayer' => ['required', 'integer', 'min:2', 'max:6'],
        ]);

        $ownerId = $request->session()->get('player_id');
        $players = [$ownerId];
        $expireAt = Carbon::now()->addDays($attributes['expireAt'])->toDateString();

        // ルーム作成
        $roomId = $createRoomService(
            $attributes['name'],
            $attributes['maxPlayer'],
            $ownerId,
            $expireAt,
            true,
            $players,
            MultiRoomController::FLAG_LIMIT);

        $room = Room::find($roomId);
        // ルーム作成時のゲーム初回作成
        $minesweeperService->initializeGame($roomId, $attributes['boardWidth'], $attributes['boardHeight'], $attributes['mineRatio']);

        return Inertia::location(route('multi.rooms.join', ['room' => $room->public_id, 'token' => $room->magic_link_token]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        //
    }
}
