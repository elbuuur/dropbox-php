<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Folder;
use App\Http\Controllers\Traits\UpdateMemoryLimitTrait;
use App\Http\Requests\TrashRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Controllers\Traits\FileStructureTrait;
use App\Http\Controllers\Traits\CacheTrait;
use function GuzzleHttp\Promise\all;

class TrashController extends Controller
{

    use UpdateMemoryLimitTrait, FileStructureTrait, CacheTrait;

    private $trashLifespan;
    private $fileModel;
    private $folderModel;


    public function __construct()
    {
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
     *                          @OA\Property(property="name", type="string", example="IMG_0514"),
     *                          @OA\Property(property="file_name", type="string", example="IMG_0514.jpg"),
     *                          @OA\Property(property="uuid", type="string", example="5a3e86e4-c09d-4594-8bf4-be8776e8769f"),
     *                          @OA\Property(property="thumb", type="string", example="http://localhost/storage/47/conversions/1-(1)-thumb.jpg")
     *                          @OA\Property(property="preview_url", type="string", example=""),
     *                          @OA\Property(property="original_url", type="string", example="http://localhost/storage/10/IMG_0514.JPG"),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="folder_id", type="integer", example=4)
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
        $folders = $user->folder()->onlyTrashed()->get()->toArray();
        $allFiles = $user->file()->onlyTrashed()->get()->toArray();
        $files = [];

        $filteredFiles = array_filter($allFiles, function ($file) use ($folders) {
            $folderId = $file['folder_id'];

            return $folderId === null || !in_array($folderId, array_column($folders, 'id'));
        });


        foreach ($filteredFiles as $file) {
            $formattedFile = $this->rememberTrashFileCache($file);
            $files[] = $formattedFile;
        }

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
                        $folder = $this->folderModel->withTrashed()->find($itemId);
                        $files = $folder->files()->onlyTrashed()->get();

                        if($files) {
                            foreach ($files as $file) {
                                $file->forceDelete();
                            }
                        }

                        $folder->forceDelete();

                        break;
                    case 'file':
                        $file = $this->fileModel->withTrashed()->find($itemId);

                        $this->invalidateFileCache($itemId);
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
            $folderIds = $user->folder()->onlyTrashed()->pluck('id')->toArray();
            $fileIds = $user->file()->onlyTrashed()->pluck('id')->toArray();

            $this->folderModel
                ->onlyTrashed()
                ->whereIn('id', $folderIds)
                ->forceDelete();

            $this->fileModel
                ->onlyTrashed()
                ->whereIn('id', $fileIds)
                ->forceDelete();

            $this->deleteAllTrashFileCache();

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

            $uploadLimit = $this->checkUploadLimit();

            foreach ($request->all() as $type => $itemId){
                switch ($type) {
                    case 'folder':
                        $folder = $this->folderModel->withTrashed()->find($itemId);
                        $folderFilesId = $folder->files()->withTrashed()->pluck('id')->toArray();

                        $folderSize = 0;

                        foreach ($folderFilesId as $fileId) {
                            $folderSize += Media::where('model_id', $fileId)->first()['size'];
                        }

                        if ($uploadLimit > $folderSize) {

                            foreach ($folderFilesId as $fileId) {
                                $this->restoreTrashFileCache($fileId);
                            }

                            $this->fileModel->withTrashed()->whereIn('id', $folderFilesId)->update(['shelf_life' => NULL]);
                            $this->fileModel->withTrashed()->whereIn('id', $folderFilesId)->restore();

                            $this->updateLimitAfterUpload($folderSize);

                            $folder->restore();
                        } else {
                            throw new \Exception('Not enough free disk space');
                        }

                        break;
                    case 'file':
                        $fileSize = Media::where('model_id', $itemId)->first()['size'];

                        if ($uploadLimit > $fileSize) {
                            $this->fileModel->withTrashed()->find($itemId)->restore();
                            $this->fileModel->withTrashed()->whereIn('id', $itemId)->update(['shelf_life' => NULL]);

                            $this->restoreTrashFileCache($itemId);
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
