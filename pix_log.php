<?php

require_once __DIR__ . '/storage.php';

header('Content-Type: application/json; charset=UTF-8');

echo json_encode(app_read_json('pix_log.json', []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
