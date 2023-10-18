<?php

namespace App\Modules\File\Repositories;

use App\Modules\File\Models\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\File\Services\MediaService;
use App\Modules\File\Services\FileStructureService;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileRepository implements FileRepositoryInterface
{
    private File $fileModel;
    private MediaService $mediaService;
    private FileStructureService $fileStructureService;

    public function __construct(
        File $fileModel,
        MediaService $mediaService,
        FileStructureService $fileStructureService,
    )
    {
        $this->fileModel = $fileModel;
        $this->mediaService = $mediaService;
        $this->fileStructureService = $fileStructureService;
    }

    public function createFile(array $data)
    {
        return $this->fileModel->create($data);
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function addAssociateMedia(File $fileModel, UploadedFile $uploadedFile): Media
    {
        return $fileModel->addMedia($uploadedFile)->toMediaCollection('file');
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

        return $this->fileStructureService->mapStructuredData($files, $mediaFiles, $thumbUrls);
    }

    public function deleteFilesByIds(array $fileIds): void
    {
        $this->fileModel->destroy($fileIds);
    }

    public function updateFile(File $file, int|null $folderId, string|null $shelfLife)
    {
        if($folderId) {
            $file->folder_id = $folderId;
        }

        if($shelfLife) {
            $file->shelf_life = $shelfLife < 0 ? NULL : now()->addDays($shelfLife)->toDateString();
        }

        $file->save();
    }

    public function deleteFile(File $file)
    {
        return $file->delete();
    }
}
