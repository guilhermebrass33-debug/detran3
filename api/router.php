<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? rtrim($path, '/') : '/';
$path = $path === '' ? '/' : $path;

$routes = [
    '/' => __DIR__ . '/../index.php',
    '/index' => __DIR__ . '/../index.php',
    '/index.php' => __DIR__ . '/../index.php',
    '/debitos' => __DIR__ . '/../debitos.php',
    '/debitos.php' => __DIR__ . '/../debitos.php',
    '/admin' => __DIR__ . '/../admin.php',
    '/admin.php' => __DIR__ . '/../admin.php',
    '/api.php' => __DIR__ . '/../api.php',
    '/api_pix.php' => __DIR__ . '/../api_pix.php',
    '/api_pix_oculto.php' => __DIR__ . '/../api_pix_oculto.php',
    '/check_admin.php' => __DIR__ . '/../check_admin.php',
    '/log_consultar.php' => __DIR__ . '/../log_consultar.php',
    '/pix_log.php' => __DIR__ . '/../pix_log.php',
    '/pix_mode.php' => __DIR__ . '/../pix_mode.php',
];

if (!isset($routes[$path])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Rota nao encontrada.';
    exit;
}

require $routes[$path];
