<?php
namespace Services;

use InvalidArgumentException;

class PasswordService {

    private int $minLength = 4;
    private int $maxLength = 128;
    private int $defaultLength = 16;

    public function generate(array $params): string {

        $length = $params['length'] ?? $this->defaultLength;

        if ($length < $this->minLength || $length > $this->maxLength) {
            throw new InvalidArgumentException("La longitud debe estar entre 4 y 128 caracteres.");
        }

        $options = [
            'upper' => filter_var($params['includeUppercase'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'lower' => filter_var($params['includeLowercase'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'digits' => filter_var($params['includeNumbers'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'symbols' => filter_var($params['includeSymbols'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'avoid_ambiguous' => filter_var($params['excludeAmbiguous'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'exclude' => $params['exclude'] ?? '',
            'require_each' => true
        ];

        return generate_password($length, $options);
    }

    public function generateMultiple(array $data): array {

        $count = $data['count'] ?? 1;
        $length = $data['length'] ?? $this->defaultLength;

        if ($count < 1 || $count > 50) {
            throw new InvalidArgumentException("El count debe estar entre 1 y 50.");
        }

        $passwords = [];

        for ($i = 0; $i < $count; $i++) {
            $passwords[] = $this->generate($data);
        }

        return $passwords;
    }

    public function validate(string $password, array $requirements): array {

        $result = [
            "valid" => true,
            "errors" => []
        ];

        if (isset($requirements['minLength']) && strlen($password) < $requirements['minLength']) {
            $result['valid'] = false;
            $result['errors'][] = "No cumple la longitud mínima";
        }

        if (!empty($requirements['requireUppercase']) && !preg_match('/[A-Z]/', $password)) {
            $result['valid'] = false;
            $result['errors'][] = "Debe contener mayúscula";
        }

        if (!empty($requirements['requireNumbers']) && !preg_match('/[0-9]/', $password)) {
            $result['valid'] = false;
            $result['errors'][] = "Debe contener número";
        }

        if (!empty($requirements['requireSymbols']) && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $result['valid'] = false;
            $result['errors'][] = "Debe contener símbolo";
        }

        return $result;
    }

}