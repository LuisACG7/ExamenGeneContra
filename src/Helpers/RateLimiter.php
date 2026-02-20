<?php

namespace Src\Helpers;

class RateLimiter
{
    private static $limit = 20; // 20 requests
    private static $timeWindow = 60; // por 60 segundos

    public static function check($ip)
    {
        $key = sys_get_temp_dir() . "/rate_limit_" . md5($ip);

        $data = [];

        if (file_exists($key)) {
            $data = json_decode(file_get_contents($key), true);
        }

        $currentTime = time();

        // Limpiar registros viejos
        $data = array_filter($data, function ($timestamp) use ($currentTime) {
            return ($timestamp > $currentTime - self::$timeWindow);
        });

        if (count($data) >= self::$limit) {
            http_response_code(429);
            echo json_encode(["error" => "Demasiadas peticiones"]);
            exit;
        }

        $data[] = $currentTime;

        file_put_contents($key, json_encode($data));
    }
}