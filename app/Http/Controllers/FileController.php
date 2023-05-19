<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UpdateMemoryLimitTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Controllers\Traits\FileStructureTrait;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;


class FileController extends Controller
{
    use FileUploadTrait, FileStructureTrait, UpdateMemoryLimitTrait;

    /**
     * Upload multiple files.
     *
     * @OA\Post(
     *     path="/api/file/upload",
     *     summary="Upload multiple files",
     *     tags={"File"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token, folder id and file (s)",
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
     *                          @OA\Property(property="id", type="integer", example=36),
     *                          @OA\Property(property="uuid", type="string", example="b28b6620-f3ed-11ed-8030-bb9a329c1263"),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="media_id", type="integer", example=12),
     *                          @OA\Property(property="folder_id", type="integer", example=4),
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
                $user = auth()->user();
                $folderId = (int)$request->folder_id;

                if($folderId && !$this->isFolderExist($folderId)) {
                    throw new \Exception('Folder does not exist');
                }

                $addedFiles = [];
                $fileSize = null;
                foreach ($request->file as $file) {
                    if ($this->phpDetect($file)) {
                        throw new \Exception('PHP files are not allowed to be uploaded');
                    };

                    $fileModel = $fileManager->create([
                        'uuid' => (string)\Webpatser\Uuid\Uuid::generate(),
                        'folder_id' => $folderId ?: null,
                        'created_by_id' => $request->user()->id
                    ]);

                    $media = $fileModel->addMedia($file)->toMediaCollection('file');
                    $addedFiles[] = $this->fileFormatData($fileModel, $media);

                    $fileSize += $media->size;
                }

                $this->updateLimitAfterUpload($user, $fileSize);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'files' => $addedFiles
                    ]
                ], 200);
            } else {
                throw new \Exception('File not found');
            }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
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
     *     description="Send bearer token, file id and file info",
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
     *                     property="file_name",
     *                     type="string",
     *                     example="summer.png"
     *                 ),
     *                 @OA\Property(
     *                     property="folder_id",
     *                     type="integer",
     *                     nullable=true,
     *                     example=4
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
     *                          @OA\Property(property="uuid", type="string", example="b28b6620-f3ed-11ed-8030-bb9a329c1263"),
     *                          @OA\Property(property="id", type="integer", example=36),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="media_id", type="integer", example=12),
     *                          @OA\Property(property="folder_id", type="integer", example=4),
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
        $fileName = str_replace(" ", "_", $request->file_name);
        $folderId = (int)$request->folder_id;
        $media = $file->getMedia('file')->first();

        if($fileName) {
            $media->file_name = $fileName;
            $media->name = $fileName;
            $media->save();
        }

        if($folderId) {
            $file->folder_id = $folderId;
            $file->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'file' => $this->fileFormatData($file, $media)
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

        $this->updateLimitAfterDelete($file);

        return response()->json([
            'status' => 'success',
            'data' => compact('file')
        ], 200);
    }
}
