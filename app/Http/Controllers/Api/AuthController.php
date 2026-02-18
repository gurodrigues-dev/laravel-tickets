<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Laravel Ticket Booking API",
 *     version="1.0.0",
 *     description="API for ticket booking system with event management and reservations"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     name="Authorization",
 *     in="header",
 *     description="Enter token in format (Bearer <token>)"
 * )
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="remember", type="boolean", example=false)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Test User"),
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials. Please check your email and password."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($email !== trim($email)) {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! $isValidEmail) {
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['The email field must be a valid email address.'],
                ],
            ], 422);
        }

        $user = \App\Models\User::whereRaw('LOWER(email) = LOWER(?)', [$email])->first();

        if (! $user) {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());

            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 422);
        }

        if (! \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($request->throttleKey());

        \Illuminate\Support\Facades\Auth::login($user, $remember);

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout successful")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Logout from the web guard (session-based)
        $webGuard = \Illuminate\Support\Facades\Auth::guard('web');
        if (method_exists($webGuard, 'logout')) {
            $webGuard->logout();
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Clear the authentication
        \Illuminate\Support\Facades\Auth::forgetGuards();

        return response()->json([
            'message' => 'Logged out',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/user",
     *     summary="Get current user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User data",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Test User"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", example="2024-01-15 10:30:00")
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
        ], 200);
    }
}
