<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Models\User;
use App\Models\Product;
use app\Core\Database;
use Exception;


class Orders
{
    private $user_id;
    private $product_id;
    private $data_compra;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function criarPedido(User $user, Product $product, int $qtd)
    {
        $this->db->beginTransaction();

        try {

            $sql = "INSERT INTO orders (user_id, product_id, data_compra) VALUES (:user_id, :product_id, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $user->id,
                ':product_id' => $product->id
            ]);

            $this->db->commit();
            return $this->db->lastInsertId();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getHistoryUser(int $id)
    {
        $sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);

        try {

            $stmt->execute([':id' => $id]);
            $total = $stmt->fetchColumn();

            return $total;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // consultas para recomendaçoes de produtos

    private function fetchOne($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute($params);

            // Se for 'column', retorna só o valor escalar (COUNT, SUM, etc.)
            if ($fetchMode === 'column') {
                return $stmt->fetchColumn();
            }

            // Retorna um objeto ou array, dependendo do modo escolhido
            return $stmt->fetch($fetchMode);
        } catch (PDOException $e) {
            error_log("Erro no banco: " . $e->getMessage());
            return null;
        }
    }


    public function totalComprasUser($user_id)
    {
        $sql = "SELECT COUNT(*) AS total_compras FROM orders WHERE user_id = :user_id";
        return (int) $this->fetchOne($sql, [':user_id' => $user_id], PDO::FETCH_OBJ);
    }

    public function catMaxBuyUser($user_id)
    {
        $sql = "
            SELECT p.categoria
            FROM orders o
            JOIN products p ON p.id = o.product_id
            WHERE o.user_id = :user_id
            GROUP BY p.categoria
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ";
        return $this->fetchOne($sql, [':user_id' => $user_id], PDO::FETCH_OBJ);
    }

    public function getProductMax($user_id)
    {
        $sql = "
            SELECT MAX(p.valor) AS produto_mais_caro
            FROM orders o
            JOIN products p ON p.id = o.product_id
            WHERE o.user_id = :user_id
                    ";
        return $this->fetchOne($sql, [':user_id' => $user_id], PDO::FETCH_OBJ);
    }

    public function productRecent($user_id)
    {
        $sql = "
            SELECT p.*
            FROM orders o
            JOIN products p ON p.id = o.product_id
            WHERE o.user_id = :user_id
            ORDER BY o.data_compra DESC
            LIMIT 1
        ";
        return $this->fetchOne($sql, [':user_id' => $user_id], PDO::FETCH_OBJ);
    }
}
