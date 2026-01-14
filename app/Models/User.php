<?php

namespace App\Models;

use App\Core\Database;
use Exception;
use PDO;
use PDOException;


class User
{
    public $id;
    private $name;
    private $email;
    private $password;
    private $role;
    private $valor;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index()
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->db->prepare($sql);

        try{
            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_CLASS, self::class);

        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO users (name, email, password, role, valor) VALUES(:name, :email, :password, :role, :valor)";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':password' => $data['password'],
                ':role' => $data['role'] ?? 'user',
                ':valor' => $data['valor']
            ]);

            $id = $this->db->lastInsertId();
            $stmt2 = $this->db->prepare("SELECT id, name, email, role, valor FROM users WHERE id = :id");
            $stmt2->execute([':id' => $id]);
            $dados = $stmt2->fetch(PDO::FETCH_ASSOC);


            if (!$dados) {
                return null;
            }

            // Preenche os atributos do próprio objeto
            foreach ($dados as $campo => $valor) {
                $this->$campo = $valor;
            }

            // Retorna o próprio objeto (classe User)
            return $this;
        } catch (PDOException $e) {
            die("Erro ao criar usuário: " . $e->getMessage());
            // return false;
        }
    }


    public function findByEmail($email): ?User
    {
        $sql = "SELECT id, name, email, password, role, valor FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([':email' => $email]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dados) {
                return null;
            }

            $user = new self();
            foreach ($dados as $campo => $valor) {
                $user->$campo = $valor;
            }

            return $user;
        } catch (PDOException $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return null;
        }
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([':id' => $id]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dados) {
                // Não encontrou, retorna null
                return null;
            }

            foreach ($dados as $campo => $valor) {
                $this->$campo = $valor;
            }

            return $this;
        } catch (PDOException $e) {
            die("Erro retornar produto: " . $e->getMessage());
        }
    }

    public function userExistsByEmail($email)
    {
        try {
            $sql = "SELECT EXISTS (SELECT 1 FROM users WHERE email = :email) AS email_existente";
            $stmt = $this->db->prepare($sql);

            $stmt->execute([':email' => $email]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (bool) $result['email_existente'];
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar e-mail: " . $e->getMessage());
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

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->execute($params);

            $stmt2 = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt2->execute([':id' => $data['id']]);

            return $stmt2->fetch(PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function destroy($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0 ){
                throw new Exception("Não foi possivel deletar usuario");
            }

            return true;
   
        } catch (PDOException $e){
            throw new Exception($e->getMessage());
        }
    }


    //

    public function atualizarSaldo($valorProduto)
    {
        if ($this->valor <= 0) {
            return false;
        }

        if ($this->valor < $valorProduto) {
            return false;
        }

        $this->valor -= $valorProduto;

        $sql = "UPDATE users SET valor = :valor WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        try {
            return $stmt->execute([
                ':valor' => $this->valor,
                ':id' => $this->id
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar saldo do usuário: " . $e->getMessage());
            return false;
        }
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'email' => $this->email ?? null,
            'role' => $this->role ?? null,
            'valor' => $this->valor ?? 0
        ];
    }


    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getEmail()
    {
        return $this->email;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getRole()
    {
        return $this->role;
    }

    public function getValor()
    {
        return $this->valor;
    }
}
