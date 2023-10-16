<?php

namespace App\Modules\File\Services;

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
}
