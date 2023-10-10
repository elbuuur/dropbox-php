<?php
namespace App\Modules\Folder\Repositories;

use App\Modules\Folder\Models\Folder;

interface FolderRepositoryInterface
{
    public function create(array $data);
    public function getFolderWithFiles(int $folderId);
}
