<?php

namespace App\Modules\File\Services;

use App\Http\Requests\UploadFileRequest;
use App\Modules\File\Models\File;
use App\Modules\File\Services\FileCacheService;
use App\Modules\File\Services\FileStructureService;
use App\Modules\Folder\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Mockery\Exception;
use App\Modules\File\Repositories\FileRepositoryInterface;
use Webpatser\Uuid\Uuid;

class FileUploadService
{
    private FileRepositoryInterface $fileRepository;
    private FileStructureService $fileStructureService;
    private FileCacheService $fileCacheService;
    private int $maxFileSize;


    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileStructureService $fileStructureService,
        FileCacheService $fileCacheService
    )
    {
        $this->fileRepository = $fileRepository;
        $this->fileStructureService = $fileStructureService;
        $this->fileCacheService = $fileCacheService;

        $this->maxFileSize = config('media-library.max_file_size');
    }

    /**
     * @param UploadedFile $file
     * @param int $folderId
     * @param int $userId
     * @param string|null $shelfLife
     * @return array
     * @throws \Exception
     */
    public function uploadFile(
        UploadedFile $file,
        int $folderId,
        int $userId,
        string|null $shelfLife
    ): array
    {
        $fileSize = $file->getSize();

        if($fileSize > $this->maxFileSize) {
            throw new \Exception('File upload limit exceeded');
        }

        if($this->isPhpFile($file)) {
            throw new \Exception('PHP files are not allowed to be uploaded');
        }

        $fileData = [
            'uuid' => (string)Uuid::generate(),
            'folder_id' => $folderId ?: null,
            'created_by_id' => $userId,
            'shelf_life' => $shelfLife
        ];


        $fileModel = $this->fileRepository->createFile($fileData);

        $media = $this->fileRepository->addAssociateMedia($fileModel, $file);

        $formattedFile = $this->fileStructureService->structureData($fileModel, $media);

        $this->fileCacheService->putFileCache($formattedFile, $fileModel->id);

        return $formattedFile;
    }

    /**
     * Check PHP file type
     * @param $file
     * @return bool
     */
    private function isPhpFile($file): bool
    {
        $extension = $file->guessExtension();
        if ($extension === 'php') {
            return true;
        }

        return false;
    }
}
