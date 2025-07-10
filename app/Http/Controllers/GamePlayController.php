<?php

namespace App\Http\Controllers;

use App\Http\Resources\MultiPlayGameResource;
use App\Models\Room;
use App\Models\RoomState;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GamePlayController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Room $room)
    {
        return Inertia::render('Multi/Play', ['data' => MultiPlayGameResource::make($room)->resolve()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
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

    // ゲーム開始処理を行う
    public function store(Request $request, Room $room)
    {
        $roomState = RoomState::where('room_id', $room->id)->first();
        $roomState->update(['status' => 'standby']);

        return response()->json(['status' => 'ok'], 201);
    }
}
