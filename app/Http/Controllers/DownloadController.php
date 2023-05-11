<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadController extends Controller
{
    /**
     * Download file by uuid Media model
     * @param $mediaUuid
     * @return Response
     */
    public function downloadFile($mediaUuid): Response
    {
        $media = Media::where('uuid', $mediaUuid)->first();
        $pathToFile = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $media->id . DIRECTORY_SEPARATOR . $media->file_name);

        return Response::download($pathToFile);
    }
}
