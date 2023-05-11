<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Http\Requests\FolderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\File;

class FolderController extends Controller
{
    /**
     * User folder create
     * @param FolderRequest $request
     * @return JsonResponse
     */
    public function create(FolderRequest $request): JsonResponse
    {
        $folder = Folder::create([
            'folder_name' => $request->folder_name,
            'created_by_id' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'data' => compact('folder')
        ], 200);
    }

    /**
     * Display the specified resource.
     * @param string $id
     * @return JsonResponse
     */
    public function index(string $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        $filesModel = $folder->files()->get();

        $files = [];
        foreach ($filesModel as $file) {
            $files[] = $file->getMedia('file');
        }

        return response()->json([
            'status' => 'success',
            'data' => compact('folder', 'files'),
        ]);
    }

    /**
     * Update the specified resource in storage.
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
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        $folder->delete();

        return response()->json([
            'status' => 'success',
            'data' => compact('folder'),
        ]);
    }
}
