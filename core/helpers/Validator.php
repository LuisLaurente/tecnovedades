<?php

namespace Core\Helpers;

class Validator
{
    public static function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isRequired($value)
    {
        return trim($value) !== '';
    }

    public static function minLength($value, $min)
    {
        return mb_strlen(trim($value)) >= $min;
    }

    public static function isNumeric($value)
    {
        return is_numeric($value);
    }
    public static function validarTexto($valor, $campo = 'Campo', $max = 100)
    {
        $valor = trim($valor);

        if (!self::isRequired($valor)) {
            throw new \Exception("❌ El campo '$campo' es obligatorio.");
        }

        if (mb_strlen($valor) > $max) {
            throw new \Exception("❌ El campo '$campo' no puede tener más de $max caracteres.");
        }

        return htmlspecialchars($valor);
    }

    public static function generarSlug($texto)
    {
        $slug = strtolower(trim($texto));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        return rtrim($slug, '-');
    }
}
