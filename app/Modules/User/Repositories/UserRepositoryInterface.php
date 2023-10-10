<?php
namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;

interface UserRepositoryInterface
{
    public function register(array $data);
    public function deleteUserTokens(User $user);
    public function decreaseUserUploadLimit(User $user, int $fileSize);
    public function increaseUserUploadLimit(User $user, int $fileSize);
}
