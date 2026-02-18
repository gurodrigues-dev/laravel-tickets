<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Services\Contracts\ReservationServiceInterface;
use Exception;

class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function store(StoreReservationRequest $request)
    {
        try {
            $reservation = $this->service->createReservation(
                $request->event_id,
                auth()->id(),
                $request->quantity,
                $request->version
            );

            return response()->json($reservation, 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        try {
            $reservation = $this->service->updateReservation(
                $id,
                $request->quantity,
                $request->version,
                auth()->id()
            );

            return response()->json($reservation);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->cancelReservation($id);

            return response()->json(['message' => 'Cancelled']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    public function myReservations()
    {
        return response()->json(
            $this->service->listUserReservations(auth()->id())
        );
    }
}
