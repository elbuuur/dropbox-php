<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteExpiredTrashFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-trash-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean trash box';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            Log::info('Start delete items in trash...');

            $lifeSpan = config('constants.TRASH_LIFESPAN');
            $trashLifespanDate = Carbon::now()->subDays($lifeSpan);

            $folderModel = new Folder();
            $fileModel = new File();

            Log::info('trashLifespanDate: ' . $trashLifespanDate);

            $deletedFolders = $folderModel
                                ->where('deleted_at', '<', $trashLifespanDate)
                                ->onlyTrashed()
                                ->get();

            if ($deletedFolders) {
                $deletedFoldersId = array_column($deletedFolders->toArray(), 'id');

                // get attachments files
                $deletedFoldersAttachmentsFiles = $fileModel
                                        ->whereIn('folder_id', $deletedFoldersId)
                                        ->withTrashed()
                                        ->get();

                $deletedFoldersAttachmentsFilesId = array_column($deletedFoldersAttachmentsFiles->toArray(), 'id');

                // delete related media
                Media::whereIn('model_id', $deletedFoldersAttachmentsFilesId)->delete();

                // delete attachments files
                $deletedFoldersAttachmentsFiles->each(function ($file) {
                    $file->forceDelete();
                });

                // delete folders
                $deletedFolders->each(function ($folder) {
                    $folder->forceDelete();
                });

                Log::info('Deleted folders: ' . $deletedFolders);
                Log::info('Deleted attachments: ' . $deletedFoldersAttachmentsFiles);
            }

            $deletedFiles = File::where('deleted_at', '<', $trashLifespanDate)->forceDelete();
            Log::info('Deleted files: ' . $deletedFiles);

        } catch (\Exception $e) {
            Log::error('Errors when deleting the recycle bin : ' . $e->getMessage());
        }
    }
}
