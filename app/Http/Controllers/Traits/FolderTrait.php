<?php

namespace App\Http\Controllers\Traits;


trait FolderTrait
{
   public function folderFormatData($folderModel, $folderSize): array
   {
       return [
           'id' => $folderModel->id,
           'folder_name' => $folderModel->folder_name,
           'folder_size' => $folderSize,
           'created_by_id' => $folderModel->created_by_id,
           'created_at' => $folderModel->created_at,
           'updated_at' => $folderModel->updated_at
       ];
   }
}
