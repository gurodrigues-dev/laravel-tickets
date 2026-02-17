<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserServiceInterface $service
    ) {}

    public function index()
    {
        return response()->json($this->service->list());
    }

    public function show($id)
    {
        return response()->json($this->service->get($id));
    }

    public function store(Request $request)
    {
        $user = $this->service->create($request->all());
        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->service->update($id, $request->all());
        return response()->json($user);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}
