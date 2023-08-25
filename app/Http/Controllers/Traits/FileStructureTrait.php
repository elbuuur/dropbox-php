<?php

namespace App\Http\Controllers\Traits;


use Illuminate\Support\Facades\Response;

trait FileStructureTrait
{
   public function fileFormatData($file, $media): array
   {
       $formattedData = [
           'file_name' => $media['file_name'],
           'uuid' => $media['uuid'],
           'id' => $media['model_id'],
           'extension' => $media['extension'],
           'size' => $media['size'],
           'media_id' => $media['id'],
           'folder_id' => $file['folder_id'],
           'shelf_life' => $file['shelf_life']
       ];

       if($thumb = $media->getUrl('thumb')) {
           $formattedData['thumb'] = $thumb;
       }

       return $formattedData;
   }
}

