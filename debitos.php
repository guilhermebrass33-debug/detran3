<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
$destination = '/debitos.html' . ($query !== '' ? '?' . $query : '');

header('Location: ' . $destination, true, 302);
exit;