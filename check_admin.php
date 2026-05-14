<?php

require_once __DIR__ . '/storage.php';

header('Content-Type: application/json; charset=UTF-8');

$adminIps = app_read_json('admin_ips.json', []);
$currentIp = app_client_ip();

echo json_encode([
    'isAdmin' => in_array($currentIp, $adminIps, true),
    'ip' => $currentIp,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
