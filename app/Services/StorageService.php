<?php

namespace App\Services;

class StorageService
{
    public function getUserStorageInfo()
    {
        return [
            'max_file_size' => config('media-library.max_file_size'),
            'system_upload_limit' => config('constants.UPLOAD_LIMIT'),
        ];
    }
}
