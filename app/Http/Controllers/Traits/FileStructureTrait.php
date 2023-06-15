<?php

namespace App\Http\Controllers\Traits;


trait FileStructureTrait
{
   public function fileFormatData($file, $media): array
   {
       return [
           'file_name' => $media['file_name'],
           'uuid' => $media['uuid'],
           'id' => $media['model_id'],
           'extension' => $media['extension'],
           'size' => $media['size'],
           'media_id' => $media['id'],
           'folder_id' => $file['folder_id'],
           'shelf_life' => $file['shelf_life']
       ];
   }
}
