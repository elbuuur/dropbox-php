<?php

namespace App\Modules\File\Repositories;

use App\Modules\File\Models\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\File\Services\MediaService;
use App\Modules\File\Services\FileStructureService;

class FileRepository implements FileRepositoryInterface
{

    private File $fileModel;
    private MediaService $mediaService;
    private FileStructureService $fileStructureService;

    public function __construct(
        File $fileModel,
        MediaService $mediaService,
        FileStructureService $fileStructureService
    )
    {
        $this->fileModel = $fileModel;
        $this->mediaService = $mediaService;
        $this->fileStructureService = $fileStructureService;
    }

    public function getFileById(int $fileId): File|Builder|array|Collection
    {
        return $this->fileModel->with('media')->find($fileId);
    }

    private function getFilesByIds($fileIds)
    {
        return $this->fileModel->whereIn('id', $fileIds)->get();
    }

    public function getFilesAndMediaInfo($fileIds): array
    {
        $files = $this->getFilesByIds($fileIds);
        $mediaFiles = $this->mediaService->getMediaByModelIds($fileIds);
        $thumbUrls = $this->mediaService->getThumbUrls($mediaFiles);

        return $this->fileStructureService->structureData($files, $mediaFiles, $thumbUrls);
    }
}
