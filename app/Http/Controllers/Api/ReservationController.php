<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Services\Contracts\ReservationServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Reservations",
 *     description="Reservation management endpoints"
 * )
 */
class ReservationController extends Controller
{
    private $service;

    public function __construct(ReservationServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reservations",
     *     summary="Create new reservation",
     *     tags={"Reservations"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"event_id", "quantity", "version"},
     *
     *             @OA\Property(property="event_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2, minimum=1),
     *             @OA\Property(property="version", type="integer", example=0, description="Optimistic locking version")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Reservation created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reservation created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="event_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="status", type="string", example="confirmed"),
     *                 @OA\Property(property="version", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or business logic error"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Event sold out or version mismatch"
     *     )
     * )
     */
    public function store(StoreReservationRequest $request): JsonResponse
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

    /**
     * @OA\Get(
     *     path="/api/v1/reservations/my-reservations",
     *     summary="Get current user's reservations",
     *     tags={"Reservations"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of user reservations",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=1),
     *                     @OA\Property(property="event", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Tech Conference 2024"),
     *                         @OA\Property(property="event_date", type="string", format="date-time")
     *                     ),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="status", type="string", example="confirmed"),
     *                     @OA\Property(property="version", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function myReservations(): JsonResponse
    {
        return response()->json(
            $this->service->listUserReservations(auth()->id())
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/reservations/{id}",
     *     summary="Update reservation",
     *     tags={"Reservations"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"quantity", "version"},
     *
     *             @OA\Property(property="quantity", type="integer", example=3, minimum=1),
     *             @OA\Property(property="version", type="integer", example=1, description="Current version for optimistic locking")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reservation updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reservation updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=3),
     *                 @OA\Property(property="version", type="integer", example=2)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Version mismatch or insufficient tickets"
     *     )
     * )
     */
    public function update(UpdateReservationRequest $request, $id): JsonResponse
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

    /**
     * @OA\Delete(
     *     path="/api/v1/reservations/{id}",
     *     summary="Cancel reservation",
     *     tags={"Reservations"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reservation cancelled successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reservation cancelled successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Reservation not found"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
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
}
