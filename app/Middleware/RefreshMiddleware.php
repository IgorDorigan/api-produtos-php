<?php

namespace App\Middleware;

use App\Services\JwtService;
use App\Utils\ApiResponse;
use Exception;

class RefreshMiddleware
{
    public static ?object $user = null;

    public static function handle()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        if (!$token) {
            ApiResponse::send(null, false, 400, "Refresh token ausente");
            exit;
        }

        // Remove prefixo "Bearer" e espaços extras
        $token = str_replace(["Bearer", "\n", "\r"], '', $token);
        $token = trim($token);

        try {
            $decoded = JwtService::validate($token);

            if (!isset($decoded->type) || $decoded->type !== "refresh") {
                ApiResponse::send(null, false, 401, "Token inválido — esperado um refresh token");
                exit;
            }

            self::$user = $decoded;
            return true;

        } catch (Exception $e) {
            $message = $e->getMessage() === "expired"
                ? "Refresh token expirado, faça login novamente"
                : "Erro ao validar refresh token.";

            ApiResponse::send(null, false, 401, $message);
            exit;
        }
    }
}

