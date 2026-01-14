<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class JwtService
{
    private static $key = 'minha-chave-secreta-super-segura-1234567890';

    public static function generate($payload)
    {
        return JWT::encode($payload, self::$key, 'HS256');
    }

    public static function validate($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$key, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}