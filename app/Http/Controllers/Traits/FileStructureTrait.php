<?php

namespace App\Http\Controllers\Traits;

trait FileStructureTrait
{
   public function fileFormatData($file, $media): array
   {
       $extensionFile = $media['extension'];
       $formattedData = [
           'file_name' => $media['file_name'],
           'uuid' => $media['uuid'],
           'id' => $media['model_id'],
           'extension' => $extensionFile,
           'size' => $media['size'],
           'media_id' => $media['id'],
           'folder_id' => $file['folder_id'],
           'shelf_life' => $file['shelf_life']
       ];

       //image-only thumbnail
       if ($extensionFile === 'png'
           || $extensionFile === 'jpg'
           || $extensionFile === 'webp'
           || $extensionFile === 'jpeg')
       {
           $formattedData['thumb'] = $media->getUrl('thumb');
       }

       return $formattedData;
   }
}

