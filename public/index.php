<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Src\Controllers\PasswordController;
use Src\Controllers\QRController;

header("Access-Control-Allow-Origin: *");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

try {

    // ========= PASSWORD API =========

    if ($uri === '/api/password' && $method === 'GET') {
        (new PasswordController())->generateSingle($_GET);
    }

    elseif ($uri === '/api/passwords' && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        (new PasswordController())->generateMultiple($data);
    }

    elseif ($uri === '/api/password/validate' && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        (new PasswordController())->validatePassword($data);
    }

    // ========= QR API =========

    elseif ($uri === '/api/qr/generate' && $method === 'POST') {
        (new QRController())->generate();
    }

    elseif ($uri === '/api/qr/types' && $method === 'GET') {
        (new QRController())->types();
    }

    // ========= 404 =========

    else {
        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode(["error" => "Endpoint no encontrado"]);
    }

} catch (Exception $e) {

    http_response_code($e->getCode() ?: 400);
    header("Content-Type: application/json");

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}