<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;
use Carbon\Carbon;

trait LoginTrait
{
    /**
     * Create token for user
     * @return User
     */
    protected function createToken(): User
    {
        $user = auth()->user();

        $user->tokens()
            ->where('name', 'apiToken')
            ->where('last_used_at', '<', Carbon::now()->modify("-1440 minutes"))
            ->delete();

        return $user;
    }
}
