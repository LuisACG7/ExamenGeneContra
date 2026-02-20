<?php

require_once __DIR__ . '/../src/Utils/GenPassword.php';
require_once __DIR__ . '/../src/Services/PasswordService.php';
require_once __DIR__ . '/../src/Controllers/PasswordController.php';
require_once __DIR__ . '/../src/Core/Response.php';

use Controllers\PasswordController;

header("Content-Type: application/json");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$controller = new PasswordController();

try {

    if ($uri === '/api/password' && $method === 'GET') {
        $controller->generateSingle($_GET);
    }

    elseif ($uri === '/api/passwords' && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $controller->generateMultiple($data);
    }

    elseif ($uri === '/api/password/validate' && $method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $controller->validatePassword($data);
    }

    else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint no encontrado"]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}