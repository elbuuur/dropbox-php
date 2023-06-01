<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;
use App\Http\Controllers\Traits\InvalidateUserCacheTrait;


trait UpdateMemoryLimitTrait
{
    use InvalidateUserCacheTrait;
    public function updateLimitAfterUpload(User $user, $fileSize): void
    {
        $user->upload_limit += $fileSize;
        $user->save();

        $this->invalidateUserCache($user->id);
    }

    public function updateLimitAfterDelete($file): void
    {
        $user = auth()->user();
        $mediaFile = $file->getMedia('file')->first();

        $user->upload_limit -= $mediaFile->size;
        $user->save();

        $this->invalidateUserCache($user->id);
    }
}
