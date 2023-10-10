<?php

namespace App\Modules\File\Services;

use App\Http\Controllers\Traits\FileStructureTrait;
use App\Modules\File\Models\File;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileCacheService
{
    use FileStructureTrait;

    private string $cacheFileTag;
    private string $cacheFileKey;
    private int $cacheFileTime;
    private string $cacheTrashTag;

    public function __construct()
    {
        $this->cacheFileTag = config('constants.FILE_CACHE_TAG');
        $this->cacheFileKey = config('constants.FILE_CACHE_KEY');
        $this->cacheFileTime = config('constants.FILE_CACHE_TIME');
        $this->cacheTrashTag = config('constants.TRASH_CACHE_TAG');
    }


    public function putFileCache($formattedFile, $fileId): void
    {
        Cache::tags($this->cacheFileTag)->put($this->cacheFileKey . $fileId, $formattedFile, now()->addMinute($this->cacheFileTime));
    }


    public function rememberTrashFileCache(array $file): array
    {
        return Cache::tags([$this->cacheFileTag, $this->cacheTrashTag])->remember($this->cacheFileKey . $file['id'], now()->addMinute($this->cacheFileTime), function () use ($file) {
            $media = Media::where('model_id', $file['id'])->first();
            return $this->fileFormatData($file, $media);
        });
    }


    public function deleteAllTrashFileCache(): void
    {
        Cache::tags($this->cacheTrashTag)->flush();
    }

    /**
     * Removing the trash tag when restoring a file
     * @param $fileId
     * @return void
     */
    public function restoreTrashFileCache($fileId): void
    {
        if (Cache::tags($this->cacheFileTag)->get($this->cacheFileKey . $fileId)) {
            $currentTags = Cache::getTagsForKey($this->cacheFileKey . $fileId);
            $newTags = array_diff($currentTags, [$this->cacheTrashTag]);
            Cache::tags($currentTags)->replaceTags($newTags);
        }
    }


    public function rememberFileCache(File $file): array
    {
        return Cache::tags($this->cacheFileTag)->remember($this->cacheFileKey . $file->id, now()->addMinute($this->cacheFileTime), function () use ($file) {
            $mediaFile = $file->getMedia('file')->first();
            return $this->fileFormatData($file, $mediaFile);
        });
    }


    public function invalidateFileCache($fileId): void
    {
        Cache::tags($this->cacheFileTag)->forget($this->cacheFileKey . $fileId);
    }


    public function getFileCache($fileId): array|NULL
    {
        return Cache::tags($this->cacheFileTag)->get($this->cacheFileKey . $fileId);
    }
}
