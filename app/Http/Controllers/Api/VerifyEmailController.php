<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Email Verification",
 *     description="Email verification endpoints"
 * )
 */
class VerifyEmailController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/verify-email/{id}/{hash}",
     *     summary="Verify user email",
     *     tags={"Email Verification"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string"),
     *         description="Verification hash"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Email verified successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Test User"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Invalid verification link"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function __invoke(Request $request, int $id, string $hash): JsonResponse
    {
        $user = \App\Models\User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return response()->json([
                'message' => 'Invalid verification link',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
                'user' => $user,
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => $user,
        ], 200);
    }
}
