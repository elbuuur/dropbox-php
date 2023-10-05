<?php

namespace App\Modules\User\Services;

use App\Http\Controllers\Traits\CacheTrait;
use App\Modules\User\Repositories\UserRepositoryInterface;

class UserMemoryLimitService
{
    use CacheTrait;
    private UserRepositoryInterface $userRepository;
    private UserCacheService $userCacheService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserCacheService $userCacheService
    )
    {
        $this->userRepository = $userRepository;
        $this->userCacheService = $userCacheService;
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

        if($cacheFile = $this->getFileCache($file->id)) {
            $fileSize = $cacheFile['size'];
            $this->userRepository->decreaseUserUploadLimit($user, $fileSize);

            $this->invalidateFileCache($file->id);
        } else {
            $mediaFile = $file->getMedia('file')->first();
            $fileSize = $mediaFile->size;

            $this->userRepository->decreaseUserUploadLimit($user, $fileSize);
        }

        $this->userCacheService->invalidateUserCache($user->id);
    }

    public function checkUploadLimit(): int|NULL
    {
        return config('constants.UPLOAD_LIMIT') - auth()->user()->upload_limit;
    }
}
