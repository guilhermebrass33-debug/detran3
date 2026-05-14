<?php
require_once __DIR__ . '/storage.php';

date_default_timezone_set('America/Sao_Paulo');

$clickStats = app_read_json('click_stats.json', [
    'consultar_clicks' => 0,
    'enter_clicks' => 0,
]);

$clickStats['enter_clicks'] = (int) ($clickStats['enter_clicks'] ?? 0) + 1;
app_write_json('click_stats.json', $clickStats);

$query = $_SERVER['QUERY_STRING'] ?? '';
$destination = '/index.html' . ($query !== '' ? '?' . $query : '');

header('Location: ' . $destination, true, 302);
exit;