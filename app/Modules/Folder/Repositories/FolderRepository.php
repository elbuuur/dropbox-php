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
}
