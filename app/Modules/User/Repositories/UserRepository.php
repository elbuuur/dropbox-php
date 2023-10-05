<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
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

    public function decreaseUserUploadLimit(User $user, int $fileSize): void
    {
        $user->upload_limit -= $fileSize;
        $user->save();
    }

    public function increaseUserUploadLimit(User $user, int $fileSize): void
    {
        $user->upload_limit += $fileSize;
        $user->save();
    }
}
