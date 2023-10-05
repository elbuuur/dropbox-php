<?php
namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;

interface UserRepositoryInterface
{
    public function register(array $data);
    public function deleteUserTokens(User $user);
}
