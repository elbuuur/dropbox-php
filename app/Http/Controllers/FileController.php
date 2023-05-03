<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\FileUploadRequest;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileController extends Controller
{
    use FileUploadTrait;

    /**
     * Show the form for creating a new resource.
     * @param FileUploadRequest $request
     * @return JsonResponse
     */
    public function create(FileUploadRequest $request): JsonResponse
    {
        if($request->hasFile('file')){

            $fileManager = new File();

            $addedFiles = [];
            foreach ($request->file as $file) {
                $fileModel = $fileManager->create([
                    'uuid' => (string)\Webpatser\Uuid\Uuid::generate(),
                    'folder_id' => $request->folder_id ?: null,
                    'created_by_id' => $request->user()->id
                ]);

                $media = $fileModel->addMedia($file)->toMediaCollection('file');
                $addedFiles[] = $media;
            }

            return response()->json([
                'status' => 'success',
                'data' => compact('addedFiles')
            ],200);
        } else {
            return response()->json(['error' => 'Failed to upload files'], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileUploadRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileRequest $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }
}
