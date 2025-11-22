<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\TokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and create API token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();
            
            // Revoke existing tokens if requested
            if ($request->boolean('revoke_existing')) {
                $user->tokens()->delete();
            }

            // Create new token
            $token = $user->createToken('api-token', ['form-test']);

            Log::info('API login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_name' => $token->accessToken->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                    ],
                    'token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $token->accessToken->expires_at?->toISOString(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new API token for the authenticated user
     */
    public function createToken(TokenRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Revoke existing tokens if requested
            if ($request->boolean('revoke_existing')) {
                $user->tokens()->delete();
            }

            // Create new token
            $token = $user->createToken(
                $request->token_name ?? 'api-token',
                $request->abilities ?? ['form-test']
            );

            Log::info('API token created', [
                'user_id' => $user->id,
                'token_name' => $token->accessToken->name,
                'abilities' => $token->accessToken->abilities,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token created successfully',
                'data' => [
                    'token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'name' => $token->accessToken->name,
                    'abilities' => $token->accessToken->abilities,
                    'expires_at' => $token->accessToken->expires_at?->toISOString(),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('API token creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user information
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                    ],
                    'tokens' => $user->tokens->map(function ($token) {
                        return [
                            'id' => $token->id,
                            'name' => $token->name,
                            'abilities' => $token->abilities,
                            'last_used_at' => $token->last_used_at?->toISOString(),
                            'expires_at' => $token->expires_at?->toISOString(),
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('API user info retrieval failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke current token
     */
    public function revokeToken(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $token = $request->user()->currentAccessToken();
            
            if ($token) {
                $token->delete();
                
                Log::info('API token revoked', [
                    'user_id' => $user->id,
                    'token_id' => $token->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Token revoked successfully',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No token to revoke',
            ], 400);

        } catch (\Exception $e) {
            Log::error('API token revocation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token revocation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke all tokens for the current user
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $tokenCount = $user->tokens()->count();
            
            $user->tokens()->delete();
            
            Log::info('All API tokens revoked', [
                'user_id' => $user->id,
                'revoked_count' => $tokenCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "All {$tokenCount} tokens revoked successfully",
            ], 200);

        } catch (\Exception $e) {
            Log::error('API token revocation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token revocation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->revokeToken($request);
    }
}
