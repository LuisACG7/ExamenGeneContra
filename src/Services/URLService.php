<?php

namespace Src\Services;

use Src\Models\URLModel;
use Exception;

class URLService
{
    private $model;

    public function __construct()
    {
        $this->model = new URLModel();
    }

    private function generateCode($length = 6)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    public function create($data)
{
    if (!isset($data['url']) || !filter_var($data['url'], FILTER_VALIDATE_URL)) {
        throw new Exception("URL inválida", 400);
    }

    $code = $this->generateCode(6);

    $this->model->create(
        $data['url'],
        $code,
        $data['expiration_date'] ?? null,
        $data['max_uses'] ?? null,
        $_SERVER['REMOTE_ADDR']
    );

    return $code;
}

    public function redirect($code)
    {
        $url = $this->model->findByCode($code);

        if (!$url) {
            throw new Exception("No encontrada", 404);
        }

        if ($url['expiration_date'] && strtotime($url['expiration_date']) < time()) {
            throw new Exception("Expirada", 410);
        }

        if ($url['max_uses'] && $url['visits'] >= $url['max_uses']) {
            throw new Exception("Límite alcanzado", 410);
        }

        $this->model->increaseVisit($code);
        $this->model->logVisit(
            $code,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        );

        return $url['original_url'];
    }

    public function stats($code)
    {
        return $this->model->getStats($code);
    }
}