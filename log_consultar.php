<?php

require_once __DIR__ . '/storage.php';

header('Content-Type: application/json; charset=UTF-8');

$clickStats = app_read_json('click_stats.json', [
    'consultar_clicks' => 0,
    'enter_clicks' => 0,
]);

$clickStats['consultar_clicks'] = (int) ($clickStats['consultar_clicks'] ?? 0) + 1;

app_write_json('click_stats.json', $clickStats);

echo json_encode([
    'success' => true,
    'consultar_clicks' => $clickStats['consultar_clicks'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
