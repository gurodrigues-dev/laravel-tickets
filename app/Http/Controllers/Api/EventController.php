<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\EventServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Events",
 *     description="Event management endpoints"
 * )
 */
class EventController extends Controller
{
    private $service;

    public function __construct(EventServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events",
     *     summary="Get paginated list of events",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number (default: 1)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (default: 10, max: 100)",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of events",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="data", type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Tech Conference 2024"),
     *                     @OA\Property(property="description", type="string", example="Annual technology conference"),
     *                     @OA\Property(property="event_date", type="string", format="date-time", example="2024-12-15T10:00:00"),
     *                     @OA\Property(property="total_tickets", type="integer", example=500),
     *                     @OA\Property(property="max_tickets_per_user", type="integer", example=5),
     *                     @OA\Property(property="status", type="string", example="upcoming"),
     *                     @OA\Property(property="available_tickets", type="integer", example=500),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=10)
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="/api/v1/events?page=1"),
     *                 @OA\Property(property="last", type="string", example="/api/v1/events?page=10"),
     *                 @OA\Property(property="prev", type="string", example=null),
     *                 @OA\Property(property="next", type="string", example="/api/v1/events?page=2")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));

        return response()->json(
            $this->service->listEvents($perPage, $page)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/events",
     *     summary="Create new event",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "description", "event_date", "total_tickets"},
     *
     *             @OA\Property(property="name", type="string", example="Tech Conference 2024"),
     *             @OA\Property(property="description", type="string", example="Annual technology conference"),
     *             @OA\Property(property="event_date", type="string", format="date-time", example="2024-12-15T10:00:00"),
     *             @OA\Property(property="total_tickets", type="integer", example=500, minimum=1),
     *             @OA\Property(property="max_tickets_per_user", type="integer", example=5, minimum=1)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tech Conference 2024"),
     *                 @OA\Property(property="description", type="string", example="Annual technology conference"),
     *                 @OA\Property(property="event_date", type="string", format="date-time"),
     *                 @OA\Property(property="total_tickets", type="integer", example=500),
     *                 @OA\Property(property="max_tickets_per_user", type="integer", example=5),
     *                 @OA\Property(property="status", type="string", example="upcoming"),
     *                 @OA\Property(property="available_tickets", type="integer", example=500)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'event_date' => 'required|date',
            'total_tickets' => 'required|integer|min:1',
        ]);

        return response()->json(
            $this->service->createEvent($request->all()),
            201
        );
    }
}
