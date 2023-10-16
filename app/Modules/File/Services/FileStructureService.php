<?php

namespace App\Modules\File\Services;

class FileStructureService
{
    public function structureData($files, $mediaFiles, $thumbUrls): array
    {
        $combined = [];

        foreach ($files as $key => $file) {
            $media = $mediaFiles->get($key);

            $combined[] = [
                'file_name' => $media['file_name'],
                'name' => $media['name'],
                'uuid' => $media['uuid'],
                'id' => $media['model_id'],
                'extension' => $media['extension'],
                'size' => $media['size'],
                'media_id' => $media['id'],
                'folder_id' => $file['folder_id'],
                'shelf_life' => $file['shelf_life']
            ];

            if($thumbUrl = $thumbUrls->get($key)) {
                $combined[$key]['thumb'] = $thumbUrl;
            }
        }

        return $combined;
    }
}
