<?php

namespace App\Services;

use App\Models\User;



class AuthService
{
    public static function authRegister(array $data, User $userModel) : ?object
    {

        if (empty($data)) {
            return null;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        

        $user = $userModel->create($data);

        if (!$user) {
            return null;
        }

        return $user ?: null;
    }

    public static function authLogin(array $data, User $userModel) : ?User
    {
        if (empty($data) || !isset($data['email'], $data['password'])) {
            return null;
        }

        $user = $userModel->findByEmail($data['email']);

        if (!$user) {
            return null;
        }

        if (! password_verify($data['password'], $user->getPassword())){
            return null;
        }

        return $user;
        
    }
}
