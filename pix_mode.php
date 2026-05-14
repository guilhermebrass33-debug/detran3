<?php

require_once __DIR__ . '/storage.php';

header('Content-Type: text/plain; charset=UTF-8');

$config = app_load_config();
echo ($config['pixMode'] ?? 'desativo') === 'ativo' ? 'ativo' : 'desativo';
