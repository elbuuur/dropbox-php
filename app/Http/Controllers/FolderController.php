<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Http\Requests\FolderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
