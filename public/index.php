<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Carrega o .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

define('URL', '/api');


use App\Http\Router;

$router = new Router(URL);

include __DIR__ . '/../routes/api.php';

$router->run();