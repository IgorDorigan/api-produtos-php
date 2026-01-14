<?php

namespace App\Utils;


class ApiResponse
{
    public static function send(array|object|null $data, $sucess = true, $httpCode = 200, $message = '')
    {
        http_response_code($httpCode);
        header('Content-Type: Application/json');

        echo json_encode([
            'data' => $data,
            'sucess' => $sucess,
            'message' => $message
        ]);
    }

    public static function receive()
    {
        $json = file_get_contents('php://input');

        $data = json_decode($json, true);

        return $data;
    }
}