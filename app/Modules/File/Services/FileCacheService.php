<?php

namespace App\Modules\File\Services;

use App\Http\Controllers\Traits\FileStructureTrait;
use App\Modules\File\Models\File;
use App\Modules\File\Services\FileInformationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Modules\File\Repositories\FileRepositoryInterface;

class FileCacheService
{
    use FileStructureTrait;

    private FileRepositoryInterface $fileRepository;
    private FileInformationService $fileInformationService;
    private string $cacheFileTag;
    private string $cacheFileKey;
    private int $cacheFileTime;
    private string $cacheTrashTag;

    public function __construct(FileRepositoryInterface $fileRepository, FileInformationService $fileInformationService)
    {
        $this->fileRepository = $fileRepository;
        $this->fileInformationService = $fileInformationService;

        $this->cacheFileTag = config('constants.FILE_CACHE_TAG');
        $this->cacheFileKey = config('constants.FILE_CACHE_KEY');
        $this->cacheFileTime = config('constants.FILE_CACHE_TIME');
        $this->cacheTrashTag = config('constants.TRASH_CACHE_TAG');
    }


    public function putFileCache($file, $fileId): void
    {
        Cache::tags($this->cacheFileTag)->put($this->cacheFileKey . $fileId, $file, now()->addMinute($this->cacheFileTime));
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

    public function invalidateFileCache($fileId): void
    {
        Cache::tags($this->cacheFileTag)->forget($this->cacheFileKey . $fileId);
    }


    public function getFileCache($fileId)
    {
        return Cache::tags($this->cacheFileTag)->get($this->cacheFileKey . $fileId);
    }

    public function loadFilesFromCacheOrDB(Collection|array $fileIds): array
    {
        $files = [];
        $uncachedFileIds = [];

        foreach ($fileIds as $fileId) {
            $cachedFile = $this->getFileCache($fileId);

            if (!$cachedFile) {
                $uncachedFileIds[] = $fileId;
            } else {
                $files[] = $cachedFile;
            }
        }

        if ($uncachedFileIds) {
            $uncachedFiles = $this->fileInformationService->getFilesAndMediaInfo($uncachedFileIds);

            foreach ($uncachedFiles as $uncachedFile) {
                $this->putFileCache($uncachedFile, $uncachedFile['id']);
            }

            $files = [...$files, ...$uncachedFiles];
        }

        return $files;
    }
}
