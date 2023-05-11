<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FileRequest;


class FileController extends Controller
{
    use FileUploadTrait;

    /**
     * Show the form for creating a new resource.
     * @param UploadFileRequest $request
     * @return JsonResponse
     */
    public function create(UploadFileRequest $request): JsonResponse
    {
        try {
            if ($request->hasFile('file')) {
                $fileManager = new File();
                $folderId = $request->folder_id;

                if($folderId && !$this->isFolderExist($folderId)) {
                    throw new \Exception('Folder does not exist');
                }

                $addedFiles = [];
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
                    $addedFiles[] = $media;
                }

                return response()->json([
                    'status' => 'success',
                    'data' => compact('addedFiles')
                ], 200);
            } else {
                throw new \Exception('File not found');
            }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param FileRequest $request
     * @param File $file
     * @return JsonResponse
     */
    public function update(FileRequest $request, File $file): JsonResponse
    {
        $fileName = str_replace(" ", "_", $request->file_name);
        $folderId = $request->folder_id;

        if($fileName) {
            $media = $file->getMedia('file')->first();
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
            'data' => compact('file')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(File $file): JsonResponse
    {
        $file->delete();

        return response()->json([
            'status' => 'success',
            'data' => compact('file')
        ], 200);
    }
}
