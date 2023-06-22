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
];
