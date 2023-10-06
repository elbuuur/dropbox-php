<?php

namespace App\Http\Controllers\Traits;

use App\Http\Requests\UploadFileRequest;
use App\Modules\Folder\Models\Folder;
use Illuminate\Http\Request;


trait FileUploadTrait
{
    /**
     * File upload trait used in controllers to upload files
     * @param UploadFileRequest $request
     * @return array|Request
     */
    public function uploadFiles(UploadFileRequest $request): array
    {
        $uploadPath = storage_path('app/public/files');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775);
        }

        $uploadResult = [];

        foreach ($request->file as $key => $file){
            if ($request->hasFile('file')) {
                $checkType = $this->phpTypeCheck($file);
                if(!$checkType) {
                    return ['status' => 'error', 'message' => 'PHP files are not allowed to be uploaded'];
                }

                $filename = time() . '-' . $file->getClientOriginalName();
                $file->move($uploadPath, $filename);

                $uploadResult[] = $uploadPath . '/' . $filename;
            }
        }

        return $uploadResult;
    }

    /**
     * Check PHP file type
     * @param $file
     * @return bool
     */
    public function phpDetect($file): bool
    {
        try {
            if (preg_match('/php/', $file->getClientMimeType())) {
                throw new \Exception();
            }
            return false;
        } catch (\Exception) {
            return true;
        }
    }

    /**
     * Check exist folder
     * @param $folderId
     * @return bool
     */
    public function isFolderExist($folderId): bool
    {
        if (Folder::find($folderId)) {
            return true;
        } else {
            return false;
        }
    }
}
