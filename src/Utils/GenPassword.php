<?php
/**
 * Generador de contraseñas seguras.
 *
 * Seguridad:
 * - Usa random_int() para entropía criptográfica.
 * - Evita str_shuffle() para mezclar; se usa Fisher–Yates con random_int().
 */

function secure_random_int_between(int $min, int $max): int {
    return random_int($min, $max);
}

function shuffle_secure(string $str): string {
    $arr = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    $n = count($arr);

    for ($i = $n - 1; $i > 0; $i--) {
        $j = secure_random_int_between(0, $i);
        $tmp = $arr[$i];
        $arr[$i] = $arr[$j];
        $arr[$j] = $tmp;
    }

    return implode('', $arr);
}

/**
 * Genera una contraseña segura.
 */
function generate_password(int $length = 16, array $opts = []): string {

    if ($length < 1) {
        throw new InvalidArgumentException("La longitud debe ser >= 1");
    }

    $opts = array_merge([
        'upper' => true,
        'lower' => true,
        'digits' => true,
        'symbols' => true,
        'avoid_ambiguous' => true,
        'exclude' => '',
        'require_each' => true,
    ], $opts);

    $sets = [];

    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $digits = '0123456789';
    $symbols = '!@#$%^&*()-_=+[]{}|;:,.<>?';
    $ambiguous = 'Il1O0o';

    if ($opts['upper']) $sets['upper'] = $upper;
    if ($opts['lower']) $sets['lower'] = $lower;
    if ($opts['digits']) $sets['digits'] = $digits;
    if ($opts['symbols']) $sets['symbols'] = $symbols;

    if (empty($sets)) {
        throw new InvalidArgumentException("Debe activarse al menos una categoría (upper/lower/digits/symbols).");
    }

    $exclude_chars = $opts['exclude'];
    if ($opts['avoid_ambiguous']) {
        $exclude_chars .= $ambiguous;
    }

    $exclude_arr = array_unique(preg_split('//u', $exclude_chars, -1, PREG_SPLIT_NO_EMPTY));
    $exclude_map = array_flip($exclude_arr);

    foreach ($sets as $k => $chars) {

        $arr = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);

        $filtered = array_values(array_filter($arr, function($c) use ($exclude_map) {
            return !isset($exclude_map[$c]);
        }));

        if (empty($filtered)) {
            throw new InvalidArgumentException(
                "Después de aplicar exclusiones, la categoría '{$k}' no tiene caracteres disponibles."
            );
        }

        $sets[$k] = implode('', $filtered);
    }

    $pool = implode('', array_values($sets));

    if ($pool === '') {
        throw new InvalidArgumentException("No hay caracteres disponibles para generar la contraseña (pool vacío).");
    }

    $password_chars = [];

    if ($opts['require_each']) {
        foreach ($sets as $chars) {
            $idx = secure_random_int_between(0, strlen($chars) - 1);
            $password_chars[] = $chars[$idx];
        }
    }

    $needed = $length - count($password_chars);

    for ($i = 0; $i < $needed; $i++) {
        $idx = secure_random_int_between(0, strlen($pool) - 1);
        $password_chars[] = $pool[$idx];
    }

    $password = implode('', $password_chars);
    return shuffle_secure($password);
}

/**
 * Genera múltiples contraseñas a la vez.
 */
function generate_passwords(int $count = 5, int $length = 16, array $opts = []): array {

    if ($count < 1) {
        throw new InvalidArgumentException("El número de contraseñas debe ser mayor a 0");
    }

    if ($count > 100) {
        throw new InvalidArgumentException("No se pueden generar más de 100 contraseñas por solicitud");
    }

    $passwords = [];

    for ($i = 0; $i < $count; $i++) {
        $passwords[] = generate_password($length, $opts);
    }

    return $passwords;
}