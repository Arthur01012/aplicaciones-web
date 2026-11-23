<?php

function validarDatosCurso(array $entrada): array
{
    $datos = [];
    foreach (['titulo', 'descripcion', 'precio'] as $campo) {
        $valor = $entrada[$campo] ?? '';
        $datos[$campo] = is_string($valor) ? trim($valor) : '';
    }

    $destacadoEntrada = $entrada['destacado'] ?? null;
    $destacado = $destacadoEntrada === '1';
    $errores = [];

    if ($datos['titulo'] === '' || mb_strlen($datos['titulo']) < 3 || mb_strlen($datos['titulo']) > 150) {
        $errores['titulo'] = 'El título es obligatorio y debe tener entre 3 y 150 caracteres.';
    }
    if ($datos['descripcion'] === '' || mb_strlen($datos['descripcion']) > 5000) {
        $errores['descripcion'] = 'La descripción es obligatoria y no debe exceder 5000 caracteres.';
    }
    if (
        $datos['precio'] === ''
        || !preg_match('/^\d+(?:\.\d{1,2})?$/D', $datos['precio'])
        || (float) $datos['precio'] > 99999999.99
    ) {
        $errores['precio'] = 'Escribe un precio entre 0 y 99,999,999.99 con hasta dos decimales.';
    }
    if ($destacadoEntrada !== null && $destacadoEntrada !== '1') {
        $errores['destacado'] = 'El valor de curso destacado no es válido.';
    }

    return [
        'datos' => $datos,
        'destacado' => $destacado,
        'errores' => $errores,
    ];
}
