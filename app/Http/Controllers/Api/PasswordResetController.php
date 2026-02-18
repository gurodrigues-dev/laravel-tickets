<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\PasswordResetServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

/**
 * @OA\Tag(
 *     name="Password Reset",
 *     description="Password reset endpoints"
 * )
 */
class PasswordResetController extends Controller
{
    public function __construct(
        private PasswordResetServiceInterface $passwordResetService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     summary="Request password reset link",
     *     tags={"Password Reset"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent (always returns 200 for security)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="If the email exists in our system, a password reset link has been sent.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = $this->passwordResetService->sendResetLink($request->email);

        return response()->json([
            'success' => true,
            'message' => 'If the email exists in our system, a password reset link has been sent.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reset-password",
     *     summary="Reset password with token",
     *     tags={"Password Reset"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="token", type="string", example="abcdef123456"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid token"
     *     )
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $this->passwordResetService->resetPassword(
                $request->email,
                $request->password,
                $request->password_confirmation,
                $request->token
            );

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?? 422);
        }
    }
}
