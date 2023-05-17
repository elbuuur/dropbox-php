<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;


trait UpdateMemoryLimitTrait
{
    public function updateLimitAfterUpload(User $user, $fileSize): void
    {
        $user->upload_limit += $fileSize;
        $user->save();
    }

    public function updateLimitAfterDelete($file): void
    {
        $user = auth()->user();
        $mediaFile = $file->getMedia('file')->first();

        $user->upload_limit -= $mediaFile->size;
        $user->save();
    }
}
