<?php

return [

    /**
     * cache time in minutes
     */

    'USER_CACHE_TIME' => env('USER_CACHE_TIME', 120),
    'FILE_CACHE_TIME' => env('FILE_CACHE_TIME', 120),


    /**
     * keys and tags for cache
     */

    'USER_CACHE_KEY' => 'user_info_',
    'USER_CACHE_TAG' => 'users',
    'FILE_CACHE_KEY' => 'file_',
    'FILE_CACHE_TAG' => 'files',
    'TRASH_CACHE_TAG' => 'trash_files',

    /**
     * trash lifetime in days
     */

    'TRASH_LIFESPAN' => env('TRASH_LIFESPAN', 10),


    /**
     * Upload limit for user in byte
     */

    'UPLOAD_LIMIT' => 1024 * 1024 * env('UPLOAD_LIMIT', 100),
];
