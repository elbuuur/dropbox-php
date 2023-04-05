<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Settings\Models\RolesActionValue;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;



class LoginController extends Controller
{
    /**
     * Auth user
     * @param LoginRequest $request
     * @return JsonResponse
     */
    protected function login(LoginRequest $request): JsonResponse
    {
        if(!auth()->attempt($request->toArray())) {
            return response()->json([
                'error' => [
                    'error_code' => 2,
                    'error_msg' => __('auth.invalid_credentials'),
                ],
            ], 401);
        }
        return $this->createToken();
    }

    /**
     * Create token for user
     * @return JsonResponse
     */
    protected function createToken(): JsonResponse
    {
        $user = auth()->user();

        $user->tokens()
            ->where('name', 'apiToken')
            ->where('last_used_at', '<', Carbon::now()->modify("-1440 minutes"))
            ->delete();

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $user->createToken('apiToken')->plainTextToken,
                'lifetime' => 1440,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ]
            ],
        ])->withHeaders([
            'Access-Control-Allow-Origin' => '*'
        ]);
    }

    /**
     * Get user information
     * @param Request $request
     * @return JsonResponse
     */
    protected function info(Request $request):JsonResponse
    {
        try {
            $token = PersonalAccessToken::findToken(explode('|', $request->header('Authorization'))[1]);

            if (empty($token)) {
                return response()->json([
                    'error' => [
                        'error_code' => 2,
                        'error_msg' => __('auth.not_found_token')
                    ]
                ], 422);
            }

            /** @var User $user */
            $user = $token->tokenable;

            return response()->json([
                'status' => 'success',
                'data' => compact('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 401);
        }
    }
}
