<?php

namespace App\Models;

use App\Core\Database;
use App\Services\ProductService;
use Exception;
use PDO;
use PDOException;

class Product
{
    public $id;
    private $nome;
    private $valor;
    private $estoque;
    private $disponivel;
    private $categoria;

    private $listaCategorias = ['Eletronicos', 'Moda', 'Acessórios', 'Casa'];

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function store($data)
    {
        $data = ProductService::prepararDados($data); // garante defaults

        $sql = "INSERT INTO products (nome, valor, estoque, disponivel, categoria)
            VALUES(:nome, :valor, :estoque, :disponivel, :categoria)";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([
                ':nome' => $data['nome'],
                ':valor' => $data['valor'],
                ':estoque' => $data['estoque'],
                ':disponivel' => $data['disponivel'],
                ':categoria' => $data['categoria']
            ]);

            $data['id'] = $this->db->lastInsertId();
            return (object)$data;
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar produto: " . $e->getMessage());
        }
    }

    public function update($data)
    {
        // Prepara o SQL dinamicamente para aceitar patch e put

        try {

            $fields = [];
            $params = [':id' => $data['id']];

            foreach ($data as $key => $value) {
                if ($key === 'id') continue;
                $fields[] = "`$key` = :$key";
                $params[":$key"] = $value;
            }

            if (empty($fields)) {
                throw new Exception("Nenhum campo para atualizar");
            }

            $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->execute($params);

            $stmt2 = $this->db->prepare("SELECT * FROM products WHERE id = :id");
            $stmt2->execute([':id' => $data['id']]);

            return $stmt2->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }



    public function findById($id)
    {
        $db = $this->db; // ou $this->db, depende da implementação
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $db->prepare($sql);

        try {
            $stmt->execute([':id' => $id]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dados) return null;

            $product = new self();
            $product->id = (int)$dados['id'];
            $product->nome = $dados['nome'];
            $product->valor = (float)$dados['valor'];
            $product->estoque = (int)$dados['estoque'];
            $product->disponivel = (bool)$dados['disponivel'];

            return $product;
        } catch (PDOException $e) {
            die("Erro retornar produto: " . $e->getMessage());
        }
    }

    public function productExistsById($id)
    {
        try {
            $sql = "SELECT EXISTS (SELECT 1 FROM products WHERE id = :id) AS id_existente";
            $stmt = $this->db->prepare($sql);

            $stmt->execute([':email' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (bool) $result['id_existente'];
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar id: " . $e->getMessage());
        }
    }


    public function updateEstoqueBanco(int $novoEstoque, int $disponivel)
    {
        $sql = "UPDATE products SET estoque = :estoque, disponivel = :disponivel WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([
                ':estoque' => $novoEstoque,
                ':disponivel' => $disponivel,
                ':id' => $this->id
            ]);
        } catch (PDOException) {
            throw new Exception("Erro ao atualizar estoque:");
            return false;
        }
    }

    public function prepareData($data)
    {
        $data['estoque'] = $data['estoque'] ?? 0;
        $data['disponivel'] = ($data['estoque'] > 0) ? 1 : 0;

        return $data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEstoque()
    {
        return $this->estoque;
    }

    public function setEstoque($qtd)
    {
        $this->estoque = $qtd;
    }

    public function getListaCategorias()
    {
        return $this->listaCategorias;
    }

    public function getName()
    {
        return $this->nome;
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }

    public function isDisponivel()
    {
        return $this->disponivel;
    }

    public function setDisponivel($resultado)
    {
        $this->disponivel = $resultado;
    }
}
