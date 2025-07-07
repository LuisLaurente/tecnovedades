<?php

function validarTexto($valor, $campo, $max = 100) {
    $valor = trim($valor);
    if (empty($valor)) {
        exit("El campo '$campo' es obligatorio.");
    }
    if (strlen($valor) > $max) {
        exit("El campo '$campo' no puede tener más de $max caracteres.");
    }
    return htmlspecialchars($valor);
}

function generarSlug($texto) {
    $slug = strtolower(trim($texto));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    return rtrim($slug, '-');
}