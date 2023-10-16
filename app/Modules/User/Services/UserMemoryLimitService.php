<?php

namespace App\Modules\User\Services;

use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\File\Services\FileCacheService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Modules\File\Services\MediaService;

class UserMemoryLimitService
{
    private UserRepositoryInterface $userRepository;
    private UserCacheService $userCacheService;
    private FileCacheService $fileCacheService;
    private MediaService $mediaService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserCacheService $userCacheService,
        FileCacheService $fileCacheService,
        MediaService $mediaService
    )
    {
        $this->userRepository = $userRepository;
        $this->userCacheService = $userCacheService;
        $this->fileCacheService = $fileCacheService;
        $this->mediaService = $mediaService;
    }

    public function updateLimitAfterUpload($fileSize): void
    {
        $user = auth()->user();

        $this->userRepository->increaseUserUploadLimit($user, $fileSize);

        $this->userCacheService->invalidateUserCache($user->id);
    }

    /**
     * @param array $fileIds
     * @return void
     */
    public function updateLimitAfterDelete(array $fileIds): void
    {
        $user = auth()->user();

        $filesSize = 0;

        foreach ($fileIds as $fileId) {
            $cachedFile = $this->fileCacheService->getFileCache($fileId);

            if($cachedFile) {
                $filesSize += $cachedFile['size'];

                continue;
            }

            $fileSize = $this->mediaService->getSizeByFileId($fileId);
            $filesSize += $fileSize;
        }

        $this->userRepository->decreaseUserUploadLimit($user, $filesSize);
    }

    public function checkUploadLimit(): int|NULL
    {
        $userUploadLimit = auth()->user()->upload_limit;
        $systemUploadLimit = config('constants.UPLOAD_LIMIT');

        return $systemUploadLimit - $userUploadLimit;
    }
}
