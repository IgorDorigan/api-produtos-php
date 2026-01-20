<?php

namespace App\Controller;

use App\Utils\ApiResponse;
use App\Services\JwtService;
use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Models\Product;
use App\Models\Orders;
use App\Services\Pagination;
use App\Helpers\Validate;
use App\Middleware\RefreshMiddleware;
use App\Services\OrderService;
use Exception;

class UserController
{
    private $user;
    private $pagination;
    private $product;

    public function __construct(User $user, Pagination $pagination, Product $product)
    {
        $this->user = $user;
        $this->pagination = $pagination;
        $this->product = $product;
    }


    public function index()
    {
        try {

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            $users = $this->user->index();

            $users = array_map(fn($u) => $u->toArray(), $users);

            $pagination = $this->pagination->setData($users, $page, $limit);

            $response = [
                'pagination' => $pagination->getInfo(),
                'data' => $pagination->getPageData()
            ];

            ApiResponse::send($response, true, 200, "Usuarios buscados com sucesso");
        } catch (Exception $e) {
            ApiResponse::send(null, false, 500, $e->getMessage());
        }
    }


    public function register()
    {
        try {
            $dados = ApiResponse::receive();

            if (empty($dados)) {
                ApiResponse::send(null, false, 400, "Dados faltando");
                exit;
            }

            $camposObrigatorios = ['name', 'email', 'password', 'valor'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    return ApiResponse::send(null, false, 400, "Campo $campo faltando");
                }
            }

            $userExistente = $this->user->findByEmail($dados['email']);
            if ($userExistente) {
                ApiResponse::send(null, false, 409, "E-mail já está cadastrado");
                exit;
            }

            $userLogado = AuthMiddleware::$user ?? null;
            $isAdmin = $userLogado && $userLogado->role === 'admin';

            if (isset($dados['role']) && !$isAdmin) {
                return ApiResponse::send(null, false, 403, "Somente administradores podem definir a role do usuário");
            }

            $dados['role'] = $dados['role'] ?? 'user';

            $createUser = AuthService::authRegister($dados, $this->user);

            if ($createUser === null) {
                ApiResponse::send(null, false, 500, "Não foi possivel registrar-se agora.");
                exit;
            }

            $createUser->setPassword(null);

            return ApiResponse::send($createUser->toArray(), true, 201, "Usuário criado com sucesso");
        } catch (\Throwable $e) {
            return ApiResponse::send(null, false, 500, "Erro interno: " . $e->getMessage());
        }
    }


    public function login()
    {
        $dados = ApiResponse::receive();

        if (empty($dados)) {
            ApiResponse::send(null, false, 400, "Dados faltando");
            exit;
        }

        if (!isset($dados['email']) || !isset($dados['password'])) {
            ApiResponse::send(null, false, 400, "Dados faltando para realizar login do usuário");
            exit;
        }

        $userLogin = AuthService::authLogin($dados, $this->user);

        if ($userLogin !== null) {

            $token = JwtService::generateTokens($userLogin);

            $userResponse = [
                'user' => $userLogin->toArray(),
                'token' => $token['access_token'],
                'refresh_token' => $token['refresh_token']
            ];

            $userLogin->setPassword(null);

            ApiResponse::send($userResponse, true, 200, "Login realizado com sucesso");
            exit;
        }

        ApiResponse::send(null, false, 400, "Credencias invalidas");
    }

    public function updateProfile() // user
    {
        try {
            $user = AuthMiddleware::$user;
            $user = $this->performUpdate($user->id, ApiResponse::receive());

            ApiResponse::send($user->toArray(), true, 200, "Perfil atualizado com sucesso");
        } catch (Exception $e) {
            $statusCode = $e->getMessage() === "campo" || $e->getMessage() === "nome"
                ? 400
                : 500;

            ApiResponse::send($user->toArray(), true, $statusCode, $e->getMessage());
        }
    }

    public function patchProfile() // user logado
    {
        try {
            $user = AuthMiddleware::$user;
            $data = ApiResponse::receive();
            $user = $this->performUpdate($user->id, $data, false);

            ApiResponse::send($user->toArray(), true, 200, "Perfil atualizado parcialmente com sucesso");
        } catch (Exception $e) {
            $statusCode = str_starts_with($e->getMessage(), "Campo") ? 400 : 500;
            ApiResponse::send(null, false, $statusCode, $e->getMessage());
        }
    }

    public function update($id) // admin
    {
        try {
            $user = $this->performUpdate($id, ApiResponse::receive());
            ApiResponse::send($user->toArray(), true, 200, "Usuário atualizado com sucesso");
        } catch (Exception $e) {
            $statusCode = $e->getMessage() === "campo" || $e->getMessage() === "nome"
                ? 400
                : 500;

            ApiResponse::send($user->toArray(), true, $statusCode, $e->getMessage());
        }
    }

    public function patch($id) // admin
    {
        try {
            $data = ApiResponse::receive();

            $user = $this->performUpdate($id, $data, false);

            ApiResponse::send($user->toArray(), true, 200, "Usuário atualizado parcialmente com sucesso");
        } catch (Exception $e) {
            $statusCode = str_starts_with($e->getMessage(), "Campo") ? 400 : 500;
            ApiResponse::send(null, false, $statusCode, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->user->destroy($id);

            ApiResponse::send(null, true, 200, "Usuario deletado com sucesso");
        } catch (Exception $e) {
            ApiResponse::send(null, false, 500, $e->getMessage());
        }
    }

    private function performUpdate($userId, $data, bool $full = true)
    {
        $data['id'] = $userId;
        $data = Validate::validateFields($data, 'user', !$full ? false : true);
        $updatedUser = $this->user->update($data);
        $updatedUser->setPassword(null);
        return $updatedUser;
    }

    // metodo responsavel por efetuar uma compra do usuario

    public function comprarProduto()
    {
        $userToken = AuthMiddleware::$user;

        if (!$userToken) {
            ApiResponse::send(null, false, 400, "Token ausente");
            return;
        }

        $dados = ApiResponse::receive();

        $userId = $dados['user_id'] ?? null;
        $productId = $dados['product_id'] ?? null;
        $quantidade = $dados['quantity'] ?? 1;


        if (!$userId || !$productId || $quantidade <= 0) {
            ApiResponse::send(null, false, 400, "Dados inválidos");
            return;
        }


        $user = $this->user->findById($userId);
        $product = $this->product->findById($productId);

        if (!$user || !$product) {
            ApiResponse::send(null, false, 404, "Usuário ou produto não econtrado");
            return;
        }

        if ($user->id !== $userToken->id) {
            ApiResponse::send(null, false, 400, "Usuario só pode realizar compras com sua conta");
            return;
        }


        try {
            // Processa a compra (regra de negócio no model Product)

            $pedido = OrderService::processarCompra($user, $product, $quantidade);

            ApiResponse::send($pedido, true, 200, "Compra feita com sucesso");
        } catch (\Throwable $e) {
            if (in_array($e->getMessage(), ["Saldo insuficiente", "Estoque insuficiente"])) {
                ApiResponse::send(null, false, 400, $e->getMessage());
                return;
            }

            ApiResponse::send(null, false, 500, "Erro interno ao processar a compra");
        }
    }

    public function refreshToken()
    {

        $decoded = RefreshMiddleware::$user;

        $user = $this->user->findById($decoded->id);

        if (!$user) {
            ApiResponse::send(null, false, 404, "Usuário não encontrado");
            exit;
        }


        $tokens = JwtService::generateTokens($user);

        $response = [
            'user' => $user->toArray(),
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token']
        ];

        ApiResponse::send($response, true, 200, "Novo token gerado com sucesso");
    }
}
