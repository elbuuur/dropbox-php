<?php

namespace App\Modules\Folder\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
{
    protected int $folderSize;

    public function __construct($resource, $folderSize)
    {
        parent::__construct($resource);
        $this->folderSize = $folderSize;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'folder_name' => $this->folder_name,
            'folder_size' => $this->whenNotNull($this->folderSize),
            'created_by_id' => $this->created_by_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

