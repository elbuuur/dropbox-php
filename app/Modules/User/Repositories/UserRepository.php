<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{

    protected User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function register(array $data)
    {
        return $this->userModel->create([
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
