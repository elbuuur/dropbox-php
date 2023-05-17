<?php

namespace App\Http\Controllers\Traits;

use App\Http\Requests\UploadFileRequest;
use Illuminate\Http\Request;
use App\Models\Folder;


trait FileStructureTrait
{
   public function formatData($file, $media): array
   {
       return [
           'file_name' => $media['file_name'],
           'uuid' => $media['uuid'],
           'id' => $media['model_id'],
           'extension' => $media['extension'],
           'size' => $media['size'],
           'media_id' => $media['id'],
           'folder_id' => $file['folder_id']
       ];
   }
}
