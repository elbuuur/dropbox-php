<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FileStructureTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\FileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Modules\File\Models\File;
use App\Modules\User\Services\UserMemoryLimitService;
use Illuminate\Http\JsonResponse;
use App\Modules\File\Services\FileCacheService;


class FileController extends Controller
{
    use FileUploadTrait, FileStructureTrait;

    private UserMemoryLimitService $userMemoryLimitService;
    private FileCacheService $fileCacheService;

    public function __construct(
        UserMemoryLimitService $userMemoryLimitService,
        FileCacheService $fileCacheService
    )
    {
        parent::__construct();

        $this->userMemoryLimitService = $userMemoryLimitService;
        $this->fileCacheService = $fileCacheService;
    }

    /**
     * Upload multiple files.
     *
     * @OA\Post(
     *     path="/api/file/upload",
     *     summary="Upload multiple files",
     *     tags={"File"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token, file storage time in days, folder id and file (s)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="folder_id",
     *                     type="string",
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="shelf_life",
     *                     type="integer",
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="file",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Uploaded file(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="files",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="file_name", type="string", example="IMG_0514.jpg"),
     *                          @OA\Property(property="name", type="string", example="IMG_0514"),
     *                          @OA\Property(property="id", type="integer", example=36),
     *                          @OA\Property(property="thumb", type="string", example="http://localhost/storage/47/conversions/1-(1)-thumb.jpg"),
     *                          @OA\Property(property="uuid", type="string", example="b28b6620-f3ed-11ed-8030-bb9a329c1263"),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="media_id", type="integer", example=12),
     *                          @OA\Property(property="folder_id", type="integer", example=4),
     *                          @OA\Property(property="shelf_life", type="string", format="date")
     *                      )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validate error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation errors")
     *         )
     *     )
     * )
     *
     * @param UploadFileRequest $request
     * @return JsonResponse
     */
    public function upload(UploadFileRequest $request): JsonResponse
    {
        try {
            if ($request->hasFile('file')) {
                $fileManager = new File();
                $folderId = (int)$request->folder_id;

                if($folderId && !$this->isFolderExist($folderId)) {
                    throw new \Exception('Folder does not exist');
                }

                $addedFiles = [];
                $filesSize = null;
                $maxFileSize = config('media-library.max_file_size');

                foreach ($request->file as $file) {
                    $fileSize = $file->getSize();

                    if($fileSize > $maxFileSize) {
                        throw new \Exception('File upload limit exceeded');
                    }

                    if ($this->phpDetect($file)) {
                        throw new \Exception('PHP files are not allowed to be uploaded');
                    }

                    $fileModel = $fileManager->create([
                        'uuid' => (string)\Webpatser\Uuid\Uuid::generate(),
                        'folder_id' => $folderId ?: null,
                        'created_by_id' => $request->user()->id,
                        'shelf_life' =>
                            $request->shelf_life
                            ? now()->addDays((int)$request->shelf_life)->toDateString()
                            : null
                    ]);

                    $media = $fileModel
                        ->addMedia($file)
                        ->toMediaCollection('file');

                    $formattedFile = $this->fileFormatData($fileModel, $media);

                    $addedFiles[] = $formattedFile;
                    $filesSize += $fileSize;

                    $this->fileCacheService->putFileCache($formattedFile, $fileModel->id);
                }

                $this->userMemoryLimitService->updateLimitAfterUpload($filesSize);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'files' => $addedFiles
                    ]
                ]);
            } else {
                throw new \Exception('File not found');
            }
        }catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => [
                    'validate' => $e->getMessage()
                ]
            ], 422);
        }
    }

    /**
     * Update file.
     *
     * @OA\Put(
     *     path="/api/file/{id}",
     *     summary="Update file",
     *     tags={"File"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token, file id and file name without extension",
     *     @OA\Parameter(
     *         description="File id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="30"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="summer"
     *                 ),
     *                 @OA\Property(
     *                     property="folder_id",
     *                     type="integer",
     *                     nullable=true,
     *                     example=4
     *                 ),
     *                 @OA\Property(
     *                     property="shelf_life",
     *                     type="integer",
     *                     nullable=true,
     *                     example=1
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Update File",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="file",
     *                      type="object",
     *                          @OA\Property(property="file_name", type="string", example="IMG_0514.jpg"),
     *                          @OA\Property(property="name", type="string", example="IMG_0514"),
     *                          @OA\Property(property="uuid", type="string", example="b28b6620-f3ed-11ed-8030-bb9a329c1263"),
     *                          @OA\Property(property="id", type="integer", example=36),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="thumb", type="string", example="http://localhost/storage/47/conversions/1-(1)-thumb.jpg"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="media_id", type="integer", example=12),
     *                          @OA\Property(property="folder_id", type="integer", example=4),
     *                          @OA\Property(property="shelf_life", type="string", format="date")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validate error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation errors")
     *         )
     *     ),
     * )
     *
     * @param FileRequest $request
     * @param File $file
     * @return JsonResponse
     */
    public function update(FileRequest $request, File $file): JsonResponse
    {
        // $request->name - file name without extension
        $name = str_replace(" ", "_", $request->name);
        $folderId = (int)$request->folder_id;
        $shelfLife = (int)$request->shelf_life;
        $media = $file->getMedia('file')->first();

        if($name) {
            $media->file_name = $name . '.' . $media->extension;
            $media->name = $name;
            $media->save();
        }

        if($folderId) {
            $file->folder_id = $folderId;
            $file->save();
        }

        if($shelfLife) {
            $file->shelf_life = $shelfLife < 0 ? NULL : now()->addDays($shelfLife)->toDateString();
            $file->save();
        }

        $formattedFile = $this->fileFormatData($file, $media);

        $this->fileCacheService->putFileCache($formattedFile, $file->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'file' => $formattedFile
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/file/{id}",
     *     summary="Delete file",
     *     tags={"File"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token and file id",
     *     @OA\Parameter(
     *         description="File id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         example="39"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Delete File",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="file",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=36),
     *                      @OA\Property(property="folder_id", type="integer", example=4),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                      @OA\Property(property="deleted_at", type="string", format="date-time")
     *                 ),
     *             )
     *         )
     *     )
     * )
     *
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(File $file): JsonResponse
    {
        $file->delete();

        $this->userMemoryLimitService->updateLimitAfterDelete([$file]);

        return response()->json([
            'status' => 'success',
            'data' => compact('file')
        ], 200);
    }
}
