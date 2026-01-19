<?php

use App\Controller\UserController;
use App\Http\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\AuthAdminMiddleware;
use App\Controller\RecommendationController;
use App\Controller\AdminController;
use App\Controller\ProductController;
use App\Middleware\RefreshMiddleware;

// Rota para listar todos os usuarios
$router->get('/users', [UserController::class, 'index']);

// Rotas reponsáveis pela Autenticaçao do usuario.
// Admin pode utilizar esta rota para criar um usuario
$router->post('/register', [UserController::class, 'register']);

// Rota responsavél pelo login
$router->post('/login', [UserController::class, 'login']);

// Rota responsavél por efetuar uma compra do usuario
$router->post('/buy', [UserController::class, 'comprarProduto'], [
    AuthMiddleware::class
]);

// Rota para o usuario gerar um novo token
$router->get('/refresh', [UserController::class, 'refreshToken'], [
    RefreshMiddleware::class
]);


// rotas do admin

// Rota para listar todos os usuarios
$router->get('/users', [UserController::class, 'index'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class

]);

// Rota responsavel por editar um usuario

$router->put('/edit/user/{id}', [UserController::class, 'update'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);

// Rora responsavel por editar parcialmente um usuario
$router->patch('/edit/user/{id}', [UserController::class, 'patch'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);

// Rora responsavel por deletar um usuario
$router->delete('/delete/{id}', [UserController::class, 'destroy'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);



// Rota para criar um produto (somente admins)

$router->post('/product', [ProductController::class, 'store'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);

// Rota para atualizar um produto

$router->put('/update/product/{id}', [ProductController::class, 'update'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);

// Rora responsavel por editar parcialmente um usuario
$router->patch('/update/product/{id}', [ProductController::class, 'patch'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);

// Rora responsavel por deletar um usuario
$router->delete('/delete/{id}', [ProductController::class, 'destroy'], [
    AuthMiddleware::class,
    AuthAdminMiddleware::class
]);


// Rota de recomendação de produtos com base nas compras do usuário
$router->get('/recommendation/{id}', [RecommendationController::class, 'getRecommendations'],[
    AuthMiddleware::class
]);



