<?php

namespace App\Middleware;

use App\Middleware\AuthMiddleware;
use App\Utils\ApiResponse;

class AuthAdminMiddleware
{
    public static function handle()
    {
        $user = AuthMiddleware::$user ?? null;

        if ($user->role !== 'admin'){
            ApiResponse::send(null, false, 401, "Acesso negado");
            exit;
        }
    }
}