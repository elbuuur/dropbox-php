<?php

namespace App\Modules\Folder\Repositories;

use App\Modules\Folder\Models\Folder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FolderRepository implements FolderRepositoryInterface
{
    protected Folder $folderModel;

    public function __construct(Folder $folderModel)
    {
        $this->folderModel = $folderModel;
    }

    public function create(array $data): Folder
    {
        return $this->folderModel->create($data);
    }

    public function getFolderWithFiles(int $folderId): Folder|Builder|Collection
    {
        return $this->folderModel
                ->with('files')
                ->findOrFail($folderId);
    }

    public function getFolderById(int $folderId): Folder
    {
        return $this->folderModel->findOrFail($folderId);
    }

    public function updateFolderName(int $folderId, string $folderName): Folder
    {
        $folder = $this->getFolderById($folderId);

        $folder->folder_name = $folderName;
        $folder->save();

        return $folder;
    }

    public function doesFolderBelongToUser(int $folderId, int $createdById)
    {
        return $this->folderModel
                    ->where('id', $folderId)
                    ->where('created_by_id', $createdById)
                    ->exists();
    }
}
