<?php


namespace App\Repositories\User;


use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositorylnterface
{

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function create($userData)
    {
        return User::create($userData);
    }
}
