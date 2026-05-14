<?php

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/pix_common.php';

try {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        $input = $_POST;
    }

    $valorRaw = $input['valor'] ?? 0;
    $descricao = isset($input['descricao']) ? trim((string) $input['descricao']) : '';
    $renavam = isset($input['renavam']) ? trim((string) $input['renavam']) : '';
    $placa = isset($input['placa']) ? trim((string) $input['placa']) : '';

    $response = pix_build_response(
        pix_selected_key('pixKey'),
        $valorRaw,
        $descricao,
        $renavam,
        $placa
    );

    if (ob_get_level() > 0) {
        ob_clean();
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_clean();
    }

    echo json_encode(['error' => $e->getMessage() ?: 'Falha interna']);
}
