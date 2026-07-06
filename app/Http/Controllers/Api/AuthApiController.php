<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Auth', 'Login and token management.')]
class AuthApiController extends Controller
{
    use ApiResponse;

    /**
     * Login and receive a Sanctum token.
     */
    #[Unauthenticated]
    #[BodyParam('email', 'string', 'The user email.', example: 'admin@telkom.com')]
    #[BodyParam('password', 'string', 'The user password.', example: 'password123')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'token' => '1|abc123...',
            'token_type' => 'Bearer',
            'user' => ['id' => 1, 'name' => 'Admin', 'email' => 'admin@telkom.com', 'role' => 'admin'],
        ],
    ])]
    #[Response(status: 401, content: ['success' => false, 'message' => 'Invalid credentials.'])]
    #[Response(status: 422, content: ['success' => false, 'message' => 'The email field is required.'])]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return $this->error('Invalid credentials.', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->name,
            ],
        ], 'Login successful');
    }

    /**
     * Revoke the current token.
     */
    #[Response(status: 200, content: ['success' => true, 'message' => 'Logged out successfully', 'data' => null])]
    #[Response(status: 401, content: ['message' => 'Unauthenticated.'])]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }
}
