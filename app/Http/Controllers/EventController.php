<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\EventServiceInterface;

class EventController extends Controller
{
    private $service;

    public function __construct(EventServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(
            $this->service->listEvents()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'event_date' => 'required|date',
            'total_tickets' => 'required|integer|min:1'
        ]);

        return response()->json(
            $this->service->createEvent($request->all()),
            201
        );
    }
}
