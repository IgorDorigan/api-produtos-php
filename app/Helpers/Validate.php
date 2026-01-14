<?php

namespace App\Helpers;

use App\Utils\ApiResponse;
use Exception;

class Validate
{

    private static array $fieldsByEntity = [
        'user' => ['name', 'email', 'role'],
        'product' => ['nome', 'categoria', 'valor']
    ];


    public static function validateFields($data, string $entity, bool $full = true)
    {
        if (!isset(self::$fieldsByEntity[$entity])) {
            throw new Exception("Entidade {$entity} não definida para validação");
        }

        $required = self::$fieldsByEntity[$entity];

        $rulesByEntity = [
            'user'      => [
                'email' => fn($v) => filter_var($v, FILTER_VALIDATE_EMAIL) ?: throw new Exception("Email inválido"),
                'name'  => fn($v) => strlen($v) > 0 ?: throw new Exception("Nome não pode ser vazio")
            ],
            'product'   => [
                'valor' => fn($v) => is_numeric($v) && $v > 0 ?: throw new Exception("Valor inválido"),
                'nome'  => fn($v) => strlen($v) > 0 ?: throw new Exception("Nome do produto não pode ser vazio")
            ]
        ];

        $rules = $rulesByEntity[$entity] ?? [];


        if ($full) {

            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    throw new Exception("Campo {$field} obrigatório para {$entity} não informado");
                }

                if (isset($rules[$field])) {
                    $rules[$field]($data[$field]);
                }
            }
        }

        foreach ($data as $field => $value) {
            if (!in_array($field, $required) && $field !== 'id') {
                throw new Exception("Campo {$field} inválido para {$entity}");
            }

            if (isset($rules[$field])) {
                $rules[$field]($value);
            }
        }

        return $data;
    }
}
