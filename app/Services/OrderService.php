<?php

namespace App\Services;

use App\Middleware\AuthMiddleware;
use App\Models\Orders;
use App\Models\Product;
use App\Models\User;
use App\Utils\ApiResponse;
use App\Services\ProductService;
use Exception;

class OrderService
{
    public static function processarCompra(User $user, Product $product, $quantidade)
    {
        if ($quantidade <= 0){
            return ApiResponse::send(null, false, 400, "Digite uma quantidade valida");
        }

        $valorTotal = $product->getValor() * $quantidade;

        if (!$user->atualizarSaldo($valorTotal)){
            throw new Exception("Saldo insuficiente");
        }

        ProductService::atualizarEstoque($product, $quantidade);
        

        $orderModel = new Orders;
        $pedido = $orderModel->criarPedido($user, $product);

        return [
            'user' => $user->toArray(),
            'product' => [
                'id' => $product->getId(),
                'nome' => $product->getName(),
                'valor' => $product->getValor(),
                'estoque' => $product->getEstoque(),
                'disponivel' => $product->isDisponivel()
            ],
            'total' => $valorTotal,
            'pedido' => $pedido
        ];
    }
}