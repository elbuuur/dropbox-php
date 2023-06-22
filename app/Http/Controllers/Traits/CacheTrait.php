<?php

namespace App\Http\Controllers\Traits;

use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Traits\FileStructureTrait;


trait CacheTrait
{
    use FileStructureTrait;

    private $cacheFileTag;
    private $cacheFileKey;
    private $cacheFileTime;
    private $cacheUserTag;
    private $cacheUserKey;
    private $cacheUserTime;

    public function __construct()
    {
        $this->cacheFileTag = config('constants.FILE_CACHE_TAG');
        $this->cacheFileKey = config('constants.FILE_CACHE_KEY');
        $this->cacheFileTime = config('constants.FILE_CACHE_TIME');
        $this->cacheUserTag = config('constants.USER_CACHE_TAG');
        $this->cacheUserKey = config('constants.USER_CACHE_KEY');
        $this->cacheUserTime = config('constants.USER_CACHE_TIME');
    }


    public function rememberUserCache($token): User
    {
        return Cache::tags($this->cacheUserTag)->remember($this->cacheUserKey . $token->tokenable->id, now()->addMinute($this->cacheUserTime), function () use ($token) {
            return $token->tokenable;
        });
    }


    public function invalidateUserCache($userId): void
    {
        Cache::tags($this->cacheUserTag)->forget($this->cacheUserKey);
    }


    public function putFileCache($formattedFile, $fileId): void
    {
        Cache::tags($this->cacheFileTag)->put($this->cacheFileKey . $fileId, $formattedFile, now()->addMinute($this->cacheFileTime));
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
}
