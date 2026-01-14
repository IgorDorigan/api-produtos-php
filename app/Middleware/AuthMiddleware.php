<?php

namespace App\Middleware;

use App\Utils\ApiResponse;
use App\Services\JwtService;


class AuthMiddleware
{
    public static ?object $user = null;

    public static function handle()
    {
        $headers = getallheaders();

        $token = $headers['Authorization'] ?? '';

        if (! $token) {
            ApiResponse::send(null, false, 400, "Token ausente");
            exit;
        }

        $token = str_replace('Bearer ', '', $token);
        $token = trim($token);

        $user = JwtService::validate($token);

        if (! $user) {
            ApiResponse::send(null, false, 400, "Token invalido ou expirado");
            exit;
        }

        self::$user = $user;

        return true;
    }
}
