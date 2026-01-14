<?php

namespace App\Services;

use App\Middleware\AuthMiddleware;
use App\Utils\ApiResponse;

use App\Helpers\DBHelper;
use App\Models\User;
use App\Models\Orders;
use Exception;

class RecommendationService
{
    public static function recommendationProduct($user_id, DBHelper $dbHelper = null)
    {
        $dbHelper = $dbHelper ?? new DBHelper();

        // Busca usuário
        $user = (new User())->findById($user_id);
        if (!$user) {
            throw new Exception("Usuário não encontrado");
        }

        $orders = new Orders();

        $totalCompra = $orders->totalComprasUser($user->id);
        $produtoMaisCaro = $orders->getProductMax($user->id);
        $produtoRecente = $orders->productRecent($user->id);

        // Se não houver pedidos ou dados insuficientes, vai para default
        if ($totalCompra === 0 || !$produtoMaisCaro || !$produtoRecente) {
            return self::getDefaultRecommendations($dbHelper);
        }

        // Categoria mais recente do usuário
        $categoriaRecente = $produtoRecente->categoria;

        $sql = "SELECT p.*
        FROM products p
        WHERE p.categoria = (
            SELECT o2_categoria.categoria
            FROM (
                SELECT pr.categoria, COUNT(*) AS total
                FROM orders o
                JOIN products pr ON o.product_id = pr.id
                WHERE o.user_id = :user_id1
                GROUP BY pr.categoria
                ORDER BY total DESC
                LIMIT 1
            ) AS o2_categoria
        )
        AND p.id NOT IN (
            SELECT product_id
            FROM orders
            WHERE user_id = :user_id2
        )
        ORDER BY p.id DESC
        LIMIT 5;";

        $recommendations = $dbHelper->fetchAll($sql, [
            ':user_id1' => $user->id,
            ':user_id2' => $user->id
        ]);

        if (!$recommendations || count($recommendations) === 0) {
            // fallback para default se nenhum produto personalizado encontrado
            $default = self::getDefaultRecommendations($dbHelper);
            return [
                'modo' => 'personalizado → fallback',
                'data' => $default['data']
            ];
        }

        return [
            'modo' => 'personalizado',
            'data' => $recommendations
        ];
    }

    public static function getDefaultRecommendations(DBHelper $dbHelper)
    {
        $sql = "SELECT * FROM products WHERE disponivel = 1 ORDER BY id LIMIT 5";
        $data = $dbHelper->fetchAll($sql);

        return [
            'modo' => 'default',
            'data' => $data
        ];
    }
}
