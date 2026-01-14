<?php

namespace App\Controller;

use App\Middleware\AuthMiddleware;
use App\Utils\ApiResponse;
use App\Services\JwtService;
use App\Services\AuthService;
use App\Models\User;
use App\Models\Product;
use App\Models\Orders;
use App\Services\RecommendationService;
use Exception;


class RecommendationController
{
        public static function getRecommendations($user_id)
        {

            $userToken = AuthMiddleware::$user;

            if (!$userToken) {
                ApiResponse::send(null, false, 401, "Token ausente");
                return;
            }

            $user = (new User())->findById($user_id);
            if (!$user) {
                ApiResponse::send(null, false, 404, "Usuário não encontrado");
                return;
            }

            if ($user->id !== $userToken->id) {
                ApiResponse::send(null, false, 403, "Usuário só pode obter recomendações da própria conta");
                return;
            }

            $totalHistory = (new Orders())->getHistoryUser($user->id);

            if ($totalHistory < 3) {
                ApiResponse::send(null, false, 400, "Usuário não atingiu o limite de compras para recomendaçoes");
                return;
            }

            try {
                // Modo opcional via GET ?modo=default ou ?modo=personalizado
                $modo = $_GET['modo'] ?? 'auto';

                switch ($modo) {
                    case 'default':
                        $recommendations = RecommendationService::getDefaultRecommendations(new \App\Helpers\DBHelper());
                        break;

                    case 'personalizado':
                        $recommendations = RecommendationService::recommendationProduct($user->id);
                        break;

                    default:
                        // auto → tenta personalizado, se falhar cai no default
                        $recommendations = RecommendationService::recommendationProduct($user->id);
                        break;
                }

                if (!$recommendations || empty($recommendations['data'])) {
                    ApiResponse::send(null, false, 404, "Nenhuma recomendação encontrada");
                    return;
                }

                ApiResponse::send(
                    $recommendations,
                    true,
                    200,
                    "Recomendações de produtos para o usuário ({$recommendations['modo']})"
                );
            } catch (Exception $e) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    "error" => true,
                    "message" => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]);
                exit;
            }
        }
}
