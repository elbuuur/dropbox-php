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

    /**
     * @param $fileIds
     * @return mixed
     */
    public function getMediaByModelIds($fileIds): mixed
    {
        return $this->media
            ->whereIn('model_id', $fileIds)
            ->get();
    }

    /**
     * @param string $uuid
     * @return Media
     */
    public function getMediaByUuid(string $uuid): Media
    {
        return $this->media->where('uuid', $uuid)->first();
    }

    /**
     * @param $media
     * @return mixed
     */
    public function getThumbUrls($media): mixed
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

    /**
     * @param array $fileIds
     * @return mixed
     */
    public function getMediaByFileIds(array $fileIds)
    {
        return $this->media->whereIn('model_id', $fileIds)->get();
    }

    /**
     * @param Media $media
     * @param string $fileName
     * @return Media
     */
    public function updateMediaName(Media $media, string $fileName): Media
    {
        $media->file_name = $fileName . '.' . $media->extension;
        $media->name = $fileName;
        $media->save();

        return $media;
    }
}
