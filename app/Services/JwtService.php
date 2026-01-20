<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Exception;

class JwtService
{
    private static $key = 'minha-chave-secreta-super-segura-1234567890';

    public static function generateTokens(User $user)
    {
        $accessPayload = [
            'id' => $user->id,
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'exp' => time() + 600
        ];

        $refreshPayload = [
            'id' => $user->id,
            'type' => 'refresh',
            'exp' => time() + 86400
        ];

        $accessToken  = JWT::encode($accessPayload, self::$key, 'HS256');
        $refreshToken = JWT::encode($refreshPayload, self::$key, 'HS256');

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    public static function validate($token)
    {
        try {
            return JWT::decode($token, new Key(self::$key, 'HS256'));
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Token expirado
            throw new Exception("expired");
        } catch (\Exception $e) {
            // Token inválido
            throw new Exception("invalid");
        }
       
    }
}