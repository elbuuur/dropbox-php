<?php

namespace App\Modules\File\Repositories;

use App\Modules\File\Models\File;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface FileRepositoryInterface
{
    public function getFileById(int $fileId);

    public function getFilesAndMediaInfo(array $fileIds);
    public function deleteFilesByIds(array $fileIds);
    public function createFile(array $data);
    public function addAssociateMedia(File $fileModel, UploadedFile $uploadedFile): Media;
    public function updateFile(File $file, int|null $folderId, string|null $shelfLife);
    public function deleteFile(File $file);
}
