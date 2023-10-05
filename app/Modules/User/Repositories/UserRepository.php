<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{

    public function register(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'upload_limit' => 0
        ]);
    }

    /**
     * Delete tokens for user
     * @param User $user
     */
    public function deleteUserTokens(User $user)
    {
        $user->tokens()->delete();
    }
}
