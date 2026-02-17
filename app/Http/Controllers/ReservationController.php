<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\ReservationServiceInterface;

class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        return response()->json(
            $this->service->createReservation(
                $request->event_id,
                auth()->id(),
                $request->quantity,
                $request->version
            ),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $this->service->updateReservation(
            $id,
            $request->quantity,
            $request->version
        );

        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $this->service->cancelReservation($id);

        return response()->json(['message' => 'Cancelled']);
    }

    public function myReservations()
    {
        return response()->json(
            $this->service->listUserReservations(auth()->id())
        );
    }
}
