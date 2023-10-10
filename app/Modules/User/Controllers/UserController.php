<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\Settings\Models\RolesActionValue;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Services\StorageService;
use App\Modules\User\Services\UserCacheService;


class UserController extends Controller
{
    private UserRepositoryInterface $userRepository;
    private StorageService $storageService;
    private UserCacheService $userCacheService;

    public function __construct(
        StorageService $storageService,
        UserRepositoryInterface $userRepository,
        UserCacheService $userCacheService
    )
    {
        parent::__construct();

        $this->storageService = $storageService;
        $this->userRepository = $userRepository;
        $this->userCacheService = $userCacheService;
    }

    /**
     * Registration a new user.
     *
     * @OA\Post(
     *     path="/register",
     *     summary="Registration a new user",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     example="johndoe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     example="12345678"
     *                 ),
     *             )
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
     *                 @OA\Property (
     *                      property="user",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="John Doe"),
     *                      @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                      @OA\Property(property="upload_limit", type="integer", example=0),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *
     *                 ),
     *                  @OA\Property(
     *                      property="storage",
     *                      type="object",
     *                      @OA\Property(property="max_file_size", type="integer", example=20971520),
     *                      @OA\Property(property="system_upload_limit", type="integer", example=104857600),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation errors"),
     *             @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="email",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The email has already been taken."
     *                      )
     *                  )
     *              )
     *         )
     *     )
     * )
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    protected function register(RegisterRequest $request): JsonResponse
    {
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = $this->userRepository->register($userData);
        $storage = $this->storageService->getUserStorageInfo();

        return response()->json([
            'status' => 'success',
            'data' => compact('user', 'storage')
        ],200);
    }

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
     *                 ),
     *                  @OA\Property(
     *                      property="storage",
     *                      type="object",
     *                      @OA\Property(property="max_file_size", type="integer", example=20971520),
     *                      @OA\Property(property="system_upload_limit", type="integer", example=104857600),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid credentials",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation errors"),
     *             @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="validate",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="Invalid credentials"
     *                      )
     *                  )
     *              )
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
                'message'   => 'Validation errors',
                'data' => [
                    'validate' => ["Invalid credentials"]
                ]
            ],401);
        }

        $user = auth()->user();

        $this->userRepository->deleteUserTokens($user);

        $userToken = $user->createToken('apiToken')->plainTextToken;

        $storage = $this->storageService->getUserStorageInfo();

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $userToken,
                'lifetime' => 1440,
                'user' => $user,
                'storage' => $storage
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
     *                 ),
     *                 @OA\Property(
     *                      property="storage",
     *                      type="object",
     *                      @OA\Property(property="max_file_size", type="integer", example=20971520),
     *                      @OA\Property(property="system_upload_limit", type="integer", example=104857600),
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
            $authorizationHeader = $request->header('Authorization');
            $tokenValue = explode('|', $authorizationHeader)[1];
            $token = PersonalAccessToken::findToken($tokenValue);

            if (empty($token)) {
                return response()->json([
                    'status' => 'error',
                    'message'   => 'Not found token',
                ], 422);
            }

            $user = $this->userCacheService->rememberUserCache($token);

            $storage = $this->storageService->getUserStorageInfo();

            return response()->json([
                'status' => 'success',
                'data' => compact('user', 'storage'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 401);
        }
    }
}
