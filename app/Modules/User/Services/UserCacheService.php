<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Cache;

class UserCacheService
{
    private string $cacheUserTag;
    private string $cacheUserKey;
    private int $cacheUserTime;

    public function __construct()
    {
        $this->cacheUserTag = config('constants.USER_CACHE_TAG');
        $this->cacheUserKey = config('constants.USER_CACHE_KEY');
        $this->cacheUserTime = config('constants.USER_CACHE_TIME');
    }

    public function rememberUserCache($token): User
    {
        return Cache::tags($this->cacheUserTag)->remember($this->cacheUserKey . $token->tokenable->id, now()->addMinute($this->cacheUserTime), function () use ($token) {
            return $token->tokenable;
        });
    }

    public function invalidateUserCache(int $userId): void
    {
        Cache::tags($this->cacheUserTag)->forget($this->cacheUserKey . $userId);
    }
}
