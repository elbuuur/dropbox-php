<?php

namespace App\Http\Controllers\Traits;

use App\Modules\User\Models\User;
use App\Http\Controllers\Traits\CacheTrait;


trait UpdateMemoryLimitTrait
{
    use CacheTrait;
    public function updateLimitAfterUpload($fileSize): void
    {
        $user = auth()->user();
        $user->upload_limit += $fileSize;
        $user->save();

        $this->invalidateUserCache($user->id);
    }

    public function updateLimitAfterDelete($file): void
    {
        $user = auth()->user();

        if($cacheFile = $this->getFileCache($file->id)) {
            $user->upload_limit -= $cacheFile['size'];
            $this->invalidateFileCache($file->id);
        } else {
            $mediaFile = $file->getMedia('file')->first();
            $user->upload_limit -= $mediaFile->size;
        }

        $user->save();

        $this->invalidateUserCache($user->id);
    }

    public function checkUploadLimit(): int|NULL
    {
        return config('constants.UPLOAD_LIMIT') - auth()->user()->upload_limit;
    }
}
