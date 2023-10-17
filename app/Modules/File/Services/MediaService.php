<?php

namespace App\Modules\File\Services;

use App\Modules\File\Models\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    private Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }


    public function getMediaByModelIds($fileIds)
    {
        return $this->media
            ->whereIn('model_id', $fileIds)
            ->get();
    }

    public function getThumbUrls($media)
    {
        return $media->map(function ($media) {
            return $media->getUrl('thumb');
        });
    }

    /**
     * @param int $fileId
     * @return int
     */
    public function getSizeByFileId(int $fileId): int
    {
        return $this->media->where('model_id', $fileId)->first()['size'];
    }
}
