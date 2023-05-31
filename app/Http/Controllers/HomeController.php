<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Folder;
use App\Http\Controllers\Traits\FileStructureTrait;

class HomeController extends Controller
{
    use FileStructureTrait;

    /**
     * Get structure from root
     *
     * @OA\Post(
     *     path="/api/home",
     *     summary="Structure from root",
     *     tags={"Root"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token",
     *     @OA\Response(
     *         response="200",
     *         description="Structure from root",
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
     *                          @OA\Property(property="size", type="integer", example=5199684),
     *                          @OA\Property(property="folder_id", type="integer", example=null),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function getStructureFromRoot(Request $request): JsonResponse
    {
        $user = $request->user();
        $folders = $user->folder;
        $filesModel = $user->file()->where('folder_id', null)->get();

        $files = [];
        foreach ($filesModel as $file) {
            $mediaFile = $file->getMedia('file')->first();
            $files[] = $this->fileFormatData($file, $mediaFile);
        }

        return response()->json([
            'status' => 'success',
            'data' => compact('folders', 'files'),
        ]);
    }
}
