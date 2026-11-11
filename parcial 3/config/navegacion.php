<?php

function renderizarMigasPan(array $elementos, string $urlCerrarSesion): void
{
    require_once __DIR__ . '/seguridad.php';
    $accionCerrarSesion = htmlspecialchars($urlCerrarSesion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    echo '<nav class="site-breadcrumbs" aria-label="Migas de pan"><ol>';
    foreach ($elementos as $indice => $elemento) {
        $texto = htmlspecialchars((string) $elemento['texto'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo '<li';
        if ($indice === array_key_last($elementos)) {
            echo ' aria-current="page">' . $texto . '</li>';
        } elseif (isset($elemento['destino_sesion'])) {
            $destino = (string) $elemento['destino_sesion'];
            if (!in_array($destino, ['inicio', 'parcial3'], true)) {
                throw new InvalidArgumentException('Destino de navegación no válido.');
            }

            $destinoSeguro = htmlspecialchars($destino, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $token = htmlspecialchars(tokenCsrf(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            echo '><form class="site-breadcrumb-action-form" action="' . $accionCerrarSesion . '" method="post">'
                . '<input type="hidden" name="token_csrf" value="' . $token . '">'
                . '<input type="hidden" name="destino" value="' . $destinoSeguro . '">'
                . '<button class="site-breadcrumb-action" type="submit">' . $texto . '</button>'
                . '</form></li>';
        } else {
            $url = htmlspecialchars((string) $elemento['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            echo '><a href="' . $url . '">' . $texto . '</a></li>';
        }
    }

    $token = htmlspecialchars(tokenCsrf(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '</ol><form class="site-breadcrumb-action-form site-breadcrumb-return-form" action="' . $accionCerrarSesion . '" method="post">'
        . '<input type="hidden" name="token_csrf" value="' . $token . '">'
        . '<input type="hidden" name="destino" value="parcial3">'
        . '<button class="site-home-action" type="submit">Volver a prácticas</button>'
        . '</form></nav>';
}
