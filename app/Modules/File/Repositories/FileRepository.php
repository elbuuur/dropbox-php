<?php

namespace App\Modules\File\Repositories;

use App\Modules\File\Models\File;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileRepository implements FileRepositoryInterface
{
    private File $fileModel;

    public function __construct(File $fileModel)
    {
        $this->fileModel = $fileModel;
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

    public function getFilesByIds($fileIds): File
    {
        return $this->fileModel->whereIn('id', $fileIds)->get();
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

    public function getUnattachedFilesId(User $user): array
    {
        return $user->file()
                    ->where('folder_id', null)
                    ->pluck('id')
                    ->toArray();
    }

    public function getDeletedFilesByIds(array $fileIds)
    {
        return $this->fileModel
                    ->onlyTrashed()
                    ->whereIn('id', $fileIds)
                    ->get();
    }

    public function getDeletedUnattachedFilesId($user): array
    {
        return $user->file()
                    ->onlyTrashed()
                    ->where('folder_id', null)
                    ->pluck('id')
                    ->toArray();
    }

    public function getDeletedFilesByFolder($folder)
    {
        return $folder->files()->onlyTrashed()->get();
    }

    public function forceDeleteByIds(array $fileIds): void
    {
        $this->fileModel
             ->whereIn('id', $fileIds)
             ->forceDelete();
    }

    public function restoreFilesByIds(array $fileIds)
    {
        $this->resetShelfLifeByIds($fileIds);
        $this->fileModel->withTrashed()->whereIn('id', $fileIds)->restore();
    }

    private function resetShelfLifeByIds(array $fileIds)
    {
        $this->fileModel->withTrashed()->whereIn('id', $fileIds)->update(['shelf_life' => NULL]);
    }
}
