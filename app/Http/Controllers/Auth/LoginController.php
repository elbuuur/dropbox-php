<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Settings\Models\RolesActionValue;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\LoginTrait;
use App\Http\Controllers\Traits\CacheTrait;


class LoginController extends Controller
{
    use LoginTrait, CacheTrait;

    /**
     * Auth user.
     *
     * @OA\Post(
     *     path="/login",
     *     summary="Auth user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret12")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="6|k8XYboI6nEOEUtoS6DUbr3jWbfSsPgkQ58wXD.."),
     *                 @OA\Property(property="lifetime", type="integer", example=1440),
     *                 @OA\Property(
     *                      property="user",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Super Dog"),
     *                      @OA\Property(property="upload_limit", type="integer", example=66464468),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    protected function login(LoginRequest $request): JsonResponse
    {
        if(!auth()->attempt($request->toArray())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ],401);
        }

        $user = $this->createToken();

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $user->createToken('apiToken')->plainTextToken,
                'lifetime' => 1440,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'upload_limit' => $user->upload_limit
                ]
            ],
        ])->withHeaders([
            'Access-Control-Allow-Origin' => '*'
        ]);
    }

    /**
     * Get user information
     *
     * @OA\Post(
     *     path="/user-info",
     *     summary="Get user information",
     *     tags={"Auth"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token",
     *     @OA\Response(
     *         response="200",
     *         description="Get user information",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="user",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Super Dog"),
     *                      @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                      @OA\Property(property="upload_limit", type="integer", example=66464468),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    protected function info(Request $request):JsonResponse
    {
        try {
            $token = PersonalAccessToken::findToken(explode('|', $request->header('Authorization'))[1]);

            if (empty($token)) {
                return response()->json([
                    'status' => 'error',
                    'message'   => 'Not found token',
                ], 422);
            }

            /** @var User $user */

            $user = $this->rememberUserCache($token);

            return response()->json([
                'status' => 'success',
                'data' => compact('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 401);
        }
    }
}
