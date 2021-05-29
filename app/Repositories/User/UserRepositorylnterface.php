<?php

namespace App\Repositories\User;


interface UserRepositorylnterface{

    public function getUserByEmail($email);

    public function create($userData);
}
