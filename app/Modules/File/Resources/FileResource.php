<?php

namespace App\Modules\File\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $media = $this['media']['0'];

//        dd($media->getUrl('thumb'));

        return [
            'file_name' => $media['file_name'],
            'name' => $media['name'],
            'uuid' => $media['uuid'],
            'id' => $media['model_id'],
            'size' => $media['size'],
            'media_id' => $media['id'],
            'folder_id' => $this['folder_id'],
            'shelf_life' => $this['shelf_life']
        ];
    }
}
