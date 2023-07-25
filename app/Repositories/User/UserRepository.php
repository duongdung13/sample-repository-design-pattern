<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        return User::findOrFail($id);
    }
}



