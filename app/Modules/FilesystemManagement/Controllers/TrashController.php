<?php

namespace App\Modules\FilesystemManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileStructureTrait;
use App\Modules\File\Models\File;
use App\Modules\File\Services\FileCacheService;
use App\Modules\FilesystemManagement\Requests\TrashRequest;
use App\Modules\Folder\Models\Folder;
use App\Modules\User\Services\UserMemoryLimitService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Modules\Folder\Repositories\FolderRepositoryInterface;
use App\Modules\File\Repositories\FileRepositoryInterface;
use App\Modules\File\Services\MediaService;

class TrashController extends Controller
{
    use FileStructureTrait;

    private UserMemoryLimitService $userMemoryLimitService;
    private FileCacheService $fileCacheService;
    private File $fileModel;
    private Folder $folderModel;
    private FolderRepositoryInterface $folderRepository;
    private FileRepositoryInterface $fileRepository;
    private MediaService $mediaService;
    private int $trashLifespan;

    public function __construct(
        UserMemoryLimitService $userMemoryLimitService,
        FileCacheService $fileCacheService,
        FolderRepositoryInterface $folderRepository,
        FileRepositoryInterface $fileRepository,
        MediaService $mediaService
    )
    {
        parent::__construct();

        $this->userMemoryLimitService = $userMemoryLimitService;
        $this->fileCacheService = $fileCacheService;
        $this->folderRepository = $folderRepository;
        $this->fileRepository = $fileRepository;
        $this->mediaService = $mediaService;

        $this->trashLifespan = config('constants.TRASH_LIFESPAN');
        $this->fileModel = new File();
        $this->folderModel = new Folder();
    }

    /**
     * Trash structure display
     *
     * @OA\Post(
     *     path="/api/trash",
     *     summary="Trash structure display",
     *     tags={"Trash"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token",
     *     @OA\Response(
     *         response="200",
     *         description="Trash structure display",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="trash_lifespan", type="integer", example=10),
     *                 @OA\Property(
     *                      property="folders",
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
     *                          @OA\Property(property="file_name", type="string", example="IMG_0514.jpg"),
     *                          @OA\Property(property="name", type="string", example="IMG_0514"),
     *                          @OA\Property(property="uuid", type="string", example="5a3e86e4-c09d-4594-8bf4-be8776e8769f"),
     *                          @OA\Property(property="thumb", type="string", example="http://localhost/storage/47/conversions/1-(1)-thumb.jpg"),
     *                          @OA\Property(property="preview_url", type="string", example=""),
     *                          @OA\Property(property="original_url", type="string", example="http://localhost/storage/10/IMG_0514.JPG"),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="folder_id", type="integer", example=4),
     *                      ),
     *                 ),
     *             )
     *         )
     *     ),
     * )
     *
     * @param TrashRequest $request
     * @return JsonResponse
     */
    public function index(TrashRequest $request): JsonResponse
    {
        $user = $request->user();
        $folders = $this->folderRepository->getDeletedFolders($user);
        $filesId = $this->fileRepository->getDeletedUnattachedFilesId($user);

        $files = $this->fileCacheService->loadFilesFromCacheOrDB($filesId, deletedFiles: true);

        return response()->json([
            'status' => 'success',
            'data' => [
                'trash_lifespan' => (int)$this->trashLifespan,
                'folders' => $folders,
                'files' => $files
            ],
        ]);
    }

    /**
     * Removing cart items
     *
     * @OA\Delete(
     *     path="/api/trash/delete",
     *     summary="Removing cart items",
     *     tags={"Trash"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token, and element ids. key-value bindings can be repeated",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="folder", type="string", example="3"),
     *             @OA\Property(property="file", type="string", example="20"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Removing cart items",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All items removed"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong..."),
     *         )
     *     ),
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteItems(Request $request): JsonResponse
    {
        try {
            if(!$request->all()) {
                throw new \Exception('No data');
            }

            foreach ($request->all() as $type => $itemId){
                switch ($type) {
                    case 'folder':
                        $folder = $this->folderRepository->getDeletedFolderById($itemId);
                        $files = $this->fileRepository->getDeletedFilesByFolder($folder);

                        if($files) {
                            foreach ($files as $file) {
                                $this->fileCacheService->invalidateFileTagCache($file->id);
                                $file->forceDelete();
                            }
                        }

                        $folder->forceDelete();

                        break;
                    case 'file':
                        $file = $this->fileRepository->getDeletedFilesByIds([$itemId]);

                        $this->fileCacheService->invalidateFileTagCache($itemId);

                        $file->forceDelete();

                        break;
                    default:
                        throw new \Exception('Something went wrong...');
                }
            }


            return response()->json([
                'status' => 'success',
                'message' => 'All items removed'
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 404);
        }
    }

    /**
     * Removing all items
     *
     * @OA\Delete(
     *     path="/api/trash/delete-all",
     *     summary="Removing all items",
     *     tags={"Trash"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token",
     *     @OA\Response(
     *         response="200",
     *         description="Removing all items",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All items removed"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong..."),
     *         )
     *     ),
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $folderIds = $this->folderRepository->getDeletedFoldersId($user);
            $fileIds = $this->fileRepository->getDeletedUnattachedFilesId($user);

            $this->folderRepository->forceDeleteByIds($folderIds);

            $this->fileRepository->forceDeleteByIds($fileIds);

            $this->fileCacheService->deleteAllTrashFileCache();

            return response()->json([
                'status' => 'success',
                'message' => 'All items removed'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 404);
        }
    }


    /**
     * Restore item(s)
     *
     * @OA\Post(
     *     path="/api/trash/restore",
     *     summary="Restore item (s)",
     *     tags={"Trash"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token, and element ids. key-value bindings can be repeated",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="folder", type="string", example="3"),
     *             @OA\Property(property="file", type="string", example="20"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Restore cart items",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All items restored"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong..."),
     *         )
     *     ),
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function restoreItems(Request $request)
    {
        try {
            if(!$request->all()) {
                throw new \Exception('No data');
            }

            $uploadLimit = $this->userMemoryLimitService->checkUploadLimit();

            foreach ($request->all() as $type => $itemId){
                switch ($type) {
                    case 'folder':
                        $folder = $this->folderRepository->getDeletedFolderById($itemId);
                        $attachedFileIds = $this->fileRepository->getDeletedFilesByFolder($folder)->pluck('id')->toArray();

                        $filesMedia = $this->mediaService->getMediaByFileIds($attachedFileIds);

                        $folderSize = collect($filesMedia)->sum('size');

                        if ($uploadLimit > $folderSize) {

                            foreach ($attachedFileIds as $fileId) {
                                $this->fileCacheService->invalidateTrashTagCache($fileId);
                            }

                            $this->fileRepository->restoreFilesByIds($attachedFileIds);

                            $this->userMemoryLimitService->updateLimitAfterUpload($folderSize);

                            $folder->restore();
                        } else {
                            throw new \Exception('Not enough free disk space');
                        }

                        break;
                    case 'file':
                        $fileSize = $this->mediaService->getSizeByFileId($itemId);

                        if ($uploadLimit > $fileSize) {
                            $this->fileRepository->restoreFilesByIds([$itemId]);

                            $this->fileCacheService->invalidateTrashTagCache($itemId);
                            
                            $this->userMemoryLimitService->updateLimitAfterUpload($fileSize);
                        } else {
                            throw new \Exception('Not enough free disk space');
                        }

                        break;
                    default:
                        throw new \Exception('Something went wrong...');
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'All items restored'
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 404);
        }
    }
}
