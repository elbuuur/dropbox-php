<?php

namespace App\Modules\Folder\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileStructureTrait;
use App\Http\Controllers\Traits\FolderTrait;
use App\Modules\File\Models\File;
use App\Modules\File\Resources\FileResource;
use App\Modules\Folder\Models\Folder;
use App\Modules\Folder\Requests\FolderRequest;
use App\Modules\User\Services\UserMemoryLimitService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use App\Modules\Folder\Repositories\FolderRepositoryInterface;
use App\Modules\File\Services\FileCacheService;
use App\Modules\Folder\Resources\FolderResource;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    use FileStructureTrait, FolderTrait;

    private FolderRepositoryInterface $folderRepository;
    private UserMemoryLimitService $userMemoryLimitService;
    private FileCacheService $fileCacheService;

    public function __construct(
        FolderRepositoryInterface $folderRepository,
        UserMemoryLimitService $userMemoryLimitService,
        FileCacheService $fileCacheService
    )
    {
        parent::__construct();

        $this->folderRepository = $folderRepository;
        $this->userMemoryLimitService = $userMemoryLimitService;
        $this->fileCacheService = $fileCacheService;
    }

    /**
     * User folder create
     *
     * @OA\Post(
     *     path="/api/folder/create",
     *     summary="Create folder",
     *     tags={"Folder"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token and folder name",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="folder_name", type="string", example="Holidays")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Folder added",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="folder",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=5),
     *                      @OA\Property(property="folder_name", type="string", example="Holidays"),
     *                      @OA\Property(property="created_by_id", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
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
     *                      property="folder_name",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The folder_name has already been taken."
     *                      )
     *                  )
     *              )
     *         )
     *     )
     * )
     *
     * @param FolderRequest $request
     * @return JsonResponse
     */
    public function create(FolderRequest $request): JsonResponse
    {
        $folderData = [
            'folder_name' => $request->folder_name,
            'created_by_id' => $request->user()->id
        ];

        $folder = $this->folderRepository->create($folderData);

        return response()->json([
            'status' => 'success',
            'data' => compact('folder')
        ], 200);
    }

    /**
     * Folder structure display
     *
     * @OA\Post(
     *     path="/api/folder/{id}",
     *     summary="Folder structure display",
     *     tags={"Folder"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token and folder id",
     *     @OA\Parameter(
     *         description="Folder id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="4"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Folder structure display",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="folder",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=5),
     *                      @OA\Property(property="folder_name", type="string", example="Holidays"),
     *                      @OA\Property(property="created_by_id", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 ),
     *                 @OA\Property(
     *                      property="files",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="name", type="string", example="IMG_0514"),
     *                          @OA\Property(property="file_name", type="string", example="IMG_0514.jpg"),
     *                          @OA\Property(property="uuid", type="string", example="5a3e86e4-c09d-4594-8bf4-be8776e8769f"),
     *                          @OA\Property(property="thumb", type="string", example="http://localhost/storage/47/conversions/1-(1)-thumb.jpg"),
     *                          @OA\Property(property="original_url", type="string", example="http://localhost/storage/10/IMG_0514.JPG"),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="folder_id", type="integer", example=4),
     *                          @OA\Property(property="shelf_life", type="string", format="date")
     *                      ),
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Folder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Folder not found or deleted"),
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return JsonResponse
     */
    public function index(string $id): JsonResponse
    {
        try {
            $folderModel = $this->folderRepository->getFolderWithFiles($id);

            $fileIds = $folderModel->files->pluck('id');

            $files = $this->fileCacheService->loadFilesFromCacheOrDB($fileIds);

            $folderSize = collect($files)->sum('size');;

            $folder = new FolderResource($folderModel, $folderSize);

            return response()->json([
                'status' => 'success',
                'data' => compact('folder', 'files'),
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Folder not found or deleted'
            ], 404);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/folder/{id}",
     *     summary="Update folder",
     *     tags={"Folder"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token and folder id",
     *     @OA\Parameter(
     *         description="Folder id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="4"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="folder_name",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Update folder",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="folder",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=5),
     *                      @OA\Property(property="folder_name", type="string", example="new name"),
     *                      @OA\Property(property="created_by_id", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 ),
     *             )
     *         )
     *     )
     * )
     *
     * @param FolderRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(FolderRequest $request, string $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        $folder->folder_name = $request->folder_name;
        $folder->save();

        return response()->json([
            'status' => 'success',
            'data' => compact('folder'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/folder/{id}",
     *     summary="Delete folder",
     *     tags={"Folder"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token and folder id",
     *     @OA\Parameter(
     *         description="Folder id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="4"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Delete folder",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="folder",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=5),
     *                      @OA\Property(property="folder_name", type="string", example="new name"),
     *                      @OA\Property(property="created_by_id", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                      @OA\Property(property="deleted_at", type="string", format="date-time")
     *                 ),
     *             )
     *         )
     *     )
     * )
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $folder = Folder::findOrFail($id);

            $filesModel = $folder->files()->get();
            foreach ($filesModel as $file) {
                $file->delete();
                $this->userMemoryLimitService->updateLimitAfterDelete($file);
            }

            $folder->delete();

            return response()->json([
                'status' => 'success',
                'data' => compact('folder'),
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Folder not found or deleted'
            ], 404);
        }

    }
}
