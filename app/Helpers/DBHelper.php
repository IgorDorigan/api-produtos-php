<?php


namespace App\Helpers;

use PDO;
use PDOException;
use App\Core\Database;


class DBHelper
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    public function fetchOne($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute($params);
            if ($fetchMode === 'column') {
                return $stmt->fetchColumn();
            }

            return $stmt->fetch($fetchMode);
        } catch (PDOException $e) {
            $e->getMessage();
            return null;
        }
    }

    public function fetchAll($sql, $params = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $stmt = $this->db->prepare($sql);

        try {
            
            $stmt->execute($params);

            $result = $stmt->fetchAll($fetchMode);

            return $result;
        
            // Neste metodo Não é possivel utilizar mais de um vez
            //o mesmo parametro dentro da quey sql, vai dar erro
            //e no caso deste contexto ira cair nas recomendaçoes deafult.


        } catch (PDOException $e) {
            echo "<pre>Erro no DBHelper: " . $e->getMessage() . "</pre>";
            return [];
        }
    }
}
