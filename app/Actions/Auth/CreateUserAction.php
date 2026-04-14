<?php

namespace App\Actions\Auth;

use App\Data\User\CreateUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function execute(CreateUserData $data): User
    {
        return User::create([
            'email' => strtolower($data->email),
            'password' => $data->password,              // password is hashed in User Model on cast
            'name' => $data->name
        ]);
    }
}
