<?php

namespace App\Modules\File\Services;

use App\Modules\File\Repositories\FileRepositoryInterface;

class FileInformationService
{
    private FileRepositoryInterface $fileRepository;
    private MediaService $mediaService;
    private FileStructureService $fileStructureService;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        MediaService $mediaService,
        FileStructureService $fileStructureService,
    )
    {
        $this->fileRepository = $fileRepository;
        $this->mediaService = $mediaService;
        $this->fileStructureService = $fileStructureService;
    }

    public function getFilesAndMediaInfo($fileIds): array
    {
        $files = $this->fileRepository->getFilesByIds($fileIds);
        $mediaFiles = $this->mediaService->getMediaByModelIds($fileIds);
        $thumbUrls = $this->mediaService->getThumbUrls($mediaFiles);

        return $this->fileStructureService->mapStructuredData($files, $mediaFiles, $thumbUrls);
    }
}
