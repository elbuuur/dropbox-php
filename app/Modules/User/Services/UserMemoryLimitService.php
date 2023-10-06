<?php

namespace App\Modules\User\Services;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\File\Services\FileCacheService;

class UserMemoryLimitService
{
    private UserRepositoryInterface $userRepository;
    private UserCacheService $userCacheService;
    private FileCacheService $fileCacheService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserCacheService $userCacheService,
        FileCacheService $fileCacheService
    )
    {
        $this->userRepository = $userRepository;
        $this->userCacheService = $userCacheService;
        $this->fileCacheService = $fileCacheService;
    }

    public function updateLimitAfterUpload($fileSize): void
    {
        $user = auth()->user();

        $this->userRepository->increaseUserUploadLimit($user, $fileSize);

        $this->userCacheService->invalidateUserCache($user->id);
    }

    public function updateLimitAfterDelete($file): void
    {
        $user = auth()->user();
        $fileId = $file->id;

        $cacheFile = $this->fileCacheService->getFileCache($fileId);

        if($cacheFile) {
            $fileSize = $cacheFile['size'];
            $this->userRepository->decreaseUserUploadLimit($user, $fileSize);
            $this->fileCacheService->invalidateFileCache($fileId);
        } else {
            $mediaFile = $file->getMedia('file')->first();
            $fileSize = $mediaFile->size;

            $this->userRepository->decreaseUserUploadLimit($user, $fileSize);
        }

        $this->userCacheService->invalidateUserCache($user->id);
    }

    public function checkUploadLimit(): int|NULL
    {
        $userUploadLimit = auth()->user()->upload_limit;
        $systemUploadLimit = config('constants.UPLOAD_LIMIT');

        return $systemUploadLimit - $userUploadLimit;
    }
}
