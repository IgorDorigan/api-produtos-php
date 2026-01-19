<?php

namespace App\Http;

use App\Http\ContainerInjection;


class Router
{

    private $routes = [];
    private $prefix;


    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }


    public function get($path, $callback, $middleware = [])
    {
        $this->routes['GET'][] = ['path' => $path, 'callback' => $callback, 'middleware' => $middleware];
    }

    public function post($path, $callback, $middleware = [])
    {
        $this->routes['POST'][] = ['path' => $path, 'callback' => $callback, 'middleware' => $middleware];
    }

    public function put($path, $callback, $middleware = [])
    {
        $this->routes['PUT'][] = ['path' => $path, 'callback' => $callback, 'middleware' => $middleware];
    }

    public function patch($path, $callback, $middleware = [])
    {
        $this->routes['PATCH'][] = ['path' => $path, 'callback' => $callback, 'middleware' => $middleware];
    }

    public function delete($path, $callback)
    {
        $this->routes['DELETE'][] = ['path' => $path, 'callback' => $callback];
    }


    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($this->prefix) {
            $prefix = rtrim($this->prefix, '/');
            if (str_starts_with($uri, $prefix)){
                $uri = substr($uri, strlen($prefix));
            }
        }

        if ($uri === '') $uri = '/';

        $method = $_SERVER['REQUEST_METHOD'];

        foreach($this->routes[$method] ?? [] as $route){
            $pattern =  preg_replace('#\{(\w+)\}#', '(\w+)', $route['path']);
            $pattern = "#^$pattern$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                foreach($route['middleware'] as $middleware) {
                    if (is_object($middleware)) {
                        $instace = $middleware;
                    }
                    else if (class_exists($middleware)) {
                        $instace = new $middleware();
                    }
                    else {
                        continue;
                    }

                    if (method_exists($instace, 'handle')) {
                        $instace->handle();
                    }
                }

                $callback = $route['callback'];

                if (is_array($callback) && class_exists($callback[0])){
                    [$class, $method] = $callback;
                    $container = new ContainerInjection();
                    $controller = $container->make($class);

                    echo call_user_func_array([$controller, $method], $matches);
                    return;
                }

                echo call_user_func_array($route['callback'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo "Página não encontrada";
    }
}
