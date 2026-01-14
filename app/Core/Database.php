<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static $instance;
    private $pdo;

    private function __construct()
    {
        
        $host = getenv('DB_HOST');
        $db = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $charset = getenv('DB_CHARSET');

        
        if (!$host || !$db || !$user || !$charset) {
            throw new Exception("Configuração do banco de dados incompleta. Verifique o arquivo .env");
        }

        
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
