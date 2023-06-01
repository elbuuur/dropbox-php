<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Cache;


trait InvalidateCacheTrait
{
    public function invalidateUserCache($userId): void
    {
        $cacheKey = 'user_info_' . $userId;
        Cache::forget($cacheKey);
    }
}
