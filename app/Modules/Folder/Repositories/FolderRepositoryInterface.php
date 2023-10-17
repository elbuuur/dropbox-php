<?php
namespace App\Modules\Folder\Repositories;

interface FolderRepositoryInterface
{
    public function create(array $data);
    public function getFolderWithFiles(int $folderId);
    public function getFolderById(int $folderId);
    public function updateFolderName(int $folderId, string $folderName);
    public function doesFolderBelongToUser(int $folderId, int $createdById);
}
