<?php

namespace App\Modules\File\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Download file by uuid Media model
     *
     * @OA\Get(
     *     path="download/{uuid}",
     *     summary="Download file",
     *     tags={"File"},
     *     security={ {"sanctum": {} }},
     *     description="Send bearer token, media uuid",
     *     @OA\Parameter(
     *         description="uuid",
     *         in="path",
     *         name="uuid",
     *         required=true,
     *         example="4ea31e34-e0cd-4aea-b489-043470f8c4e5"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File",
     *     )
     * )
     *
     * @param $mediaUuid
     * @return Response|BinaryFileResponse
     */
    public function downloadFile($mediaUuid): Response|BinaryFileResponse
    {
        $media = Media::where('uuid', $mediaUuid)->first();
        $pathToFile = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $media->id . DIRECTORY_SEPARATOR . $media->file_name);

        return Response::download($pathToFile);
    }
}
