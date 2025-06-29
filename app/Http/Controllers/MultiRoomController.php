<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomIndexResource;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MultiRoomController extends Controller
{
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
        return Inertia::render('Multi/Create', ['status' => session('status')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
