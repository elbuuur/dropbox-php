<?php

namespace App\Modules\File\Repositories;

use App\Modules\File\Models\File;

interface FileRepositoryInterface
{
    public function getFileById(int $fileId);

    public function getFilesAndMediaInfo(array $fileIds);
    public function deleteFilesByIds(array $fileIds);
}
