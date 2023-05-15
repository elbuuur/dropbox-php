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
     *                          @OA\Property(property="preview_url", type="string", example=""),
     *                          @OA\Property(property="original_url", type="string", example="http://localhost/storage/10/IMG_0514.JPG"),
     *                          @OA\Property(property="extension", type="string", example="JPG"),
     *                          @OA\Property(property="size", type="integer", example=5199684)
     *                      ),
     *                 ),
     *             )
     *         )
     *     )
     * )
     *
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
        $folder = Folder::findOrFail($id);
        $folder->delete();

        return response()->json([
            'status' => 'success',
            'data' => compact('folder'),
        ]);
    }
}
