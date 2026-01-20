<?php

namespace App\Controller;

use App\Core\Database;
use App\Helpers\Validade;
use App\Models\Product;
use App\Utils\ApiResponse;
use App\Helpers\Validate;
use App\Services\ProductService;
use Exception;

class ProductController
{
    private $valor;

    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function store()
    {
        try {
            $data = ApiResponse::receive();
            
            $product = ProductService::storeProduct($data, $this->product);
            ApiResponse::send($product, true, 201, "Produto criado com sucesso");
        } catch (Exception $e) {
            ApiResponse::send(null, false, 400, $e->getMessage());
        }
    }

    public function update($id)
    {
        try {
            $data = ApiResponse::receive();

            $data['id'] = $id;

            $data = Validate::validateFields($data, 'product');

            $product = $this->product->update($data);

            ApiResponse::send($product, true, 200, "Produto atualizado com sucesso");
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $status = str_starts_with($msg, "Campo") ? 400 : 500;
            ApiResponse::send(null, false, $status, $msg);
        }
    }

     public function patch($id)
    {
        try {
            $data = ApiResponse::receive();

            $data['id'] = $id;

            $data = Validate::validateFields($data, 'product', false);

            $product = $this->product->update($data);

            ApiResponse::send($product, true, 200, "Produto atualizado com sucesso");
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $status = str_starts_with($msg, "Campo") ? 400 : 500;
            ApiResponse::send(null, false, $status, $msg);
        }
    }



    public function setValor($preco)
    {
        if ($preco <= 0) {
            return false;
        }

        $this->valor = $preco;
        return true;
    }
}
