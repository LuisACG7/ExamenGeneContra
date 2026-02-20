<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Src\Controllers\PasswordController;
use Src\Controllers\QRController;
use Src\Controllers\UrlController; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/index.php', '', $uri); 
$uri = rtrim($uri, '/');
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

    // ========= URL SHORTENER API =========

    elseif ($uri === '/api/shorten' && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        (new UrlController())->shorten($data);
    }

    elseif (preg_match('#^/api/stats/([a-zA-Z0-9]+)$#', $uri, $matches) && $method === 'GET') {
        (new UrlController())->stats($matches[1]);
    }

    // ========= REDIRECT =========

    elseif (preg_match('#^/([a-zA-Z0-9]{6})$#', $uri, $matches) && $method === 'GET') {
        (new UrlController())->redirect($matches[1]);
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