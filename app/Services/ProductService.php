<?php

namespace App\Services;

use App\Models\Product;
use App\Helpers\Validate;
use Exception;

class ProductService
{
    public static function storeProduct($data)
    {
        Validate::validateFields($data, 'product');

        // Pode ter regras extras se precisar
        if ($data['valor'] <= 0) {
            throw new Exception("O valor do produto deve ser maior que zero");
        }

        // Criação do produto no Model
        return (new Product())->store($data);
    }
    

    public static function atualizarEstoque(Product $product ,int $quantidade)
    {   

        if ($quantidade <= 0 || $product->getEstoque() < $quantidade) {
            throw new Exception("Quantidade inválida ou estoque indisponivél");
        }

        $novoEstoque = $product->getEstoque() - $quantidade;
        $disponivel = $novoEstoque > 0 ? 1 : 0;

        $result = $product->updateEstoqueBanco($novoEstoque, $disponivel);

        if (!$disponivel){
            throw new Exception("Erro ao atualizar estoque no banco");
        }

        $product->setEstoque($novoEstoque);
        $product->setDisponivel($disponivel);

        return true;

    }

    public static function prepararDados(array $data): array
    {
        return [
        'nome' => $data['nome'] ,
        'valor' => $data['valor'], 
        'estoque' => $data['estoque'] ?? 0,
        'disponivel' => ($data['estoque'] ?? 0) > 0 ? 1 : 0,
        'categoria' => $data['categoria'] ?? ''
        ];
    }
}