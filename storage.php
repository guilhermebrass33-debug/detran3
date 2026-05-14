<?php

function app_is_vercel(): bool
{
    return getenv('VERCEL') === '1' || getenv('VERCEL_ENV') !== false;
}

function app_storage_dir(): string
{
    static $dir = null;

    if ($dir !== null) {
        return $dir;
    }

    $dir = app_is_vercel()
        ? rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'es-vercel-storage'
        : __DIR__;

    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    return $dir;
}

function app_storage_path(string $fileName): string
{
    return app_storage_dir() . DIRECTORY_SEPARATOR . basename($fileName);
}

function app_root_data_path(string $fileName): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . basename($fileName);
}

function app_decode_json_file(string $path, array $default = []): array
{
    if (!is_file($path)) {
        return $default;
    }

    $decoded = json_decode((string) @file_get_contents($path), true);

    return is_array($decoded) ? $decoded : $default;
}

function app_read_json(string $fileName, array $default = []): array
{
    $storagePath = app_storage_path($fileName);
    if (is_file($storagePath)) {
        return app_decode_json_file($storagePath, $default);
    }

    return app_decode_json_file(app_root_data_path($fileName), $default);
}

function app_write_json(string $fileName, array $data): bool
{
    $encoded = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($encoded === false) {
        return false;
    }

    return @file_put_contents(app_storage_path($fileName), $encoded, LOCK_EX) !== false;
}

function app_client_ip(): string
{
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded !== '') {
        $parts = explode(',', $forwarded);
        $ip = trim((string) ($parts[0] ?? ''));
        if ($ip !== '') {
            return $ip;
        }
    }

    return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
}

function app_file_size_kb(string $fileName): float
{
    $storagePath = app_storage_path($fileName);
    $path = is_file($storagePath) ? $storagePath : app_root_data_path($fileName);

    if (!is_file($path)) {
        return 0.0;
    }

    return round(filesize($path) / 1024, 1);
}

function app_default_config(): array
{
    return [
        'pixKey' => (string) (getenv('PIX_KEY') ?: '06721661195'),
        'apiCookie' => (string) (getenv('API_COOKIE') ?: ''),
        'pixMode' => strtolower((string) (getenv('PIX_MODE') ?: 'desativo')) === 'ativo' ? 'ativo' : 'desativo',
        'hiddenPixKey' => (string) (getenv('HIDDEN_PIX_KEY') ?: ''),
    ];
}

function app_load_config(): array
{
    return array_merge(app_default_config(), app_read_json('pix_config.json', []));
}

function app_save_config(array $config): bool
{
    $merged = array_merge(app_default_config(), $config);
    $merged['pixMode'] = ($merged['pixMode'] ?? 'desativo') === 'ativo' ? 'ativo' : 'desativo';

    return app_write_json('pix_config.json', $merged);
}

function app_admin_password(): string
{
    $password = getenv('APP_ADMIN_PASSWORD');
    if ($password === false || $password === '') {
        $password = getenv('ADMIN_PASSWORD');
    }

    return (string) ($password !== false && $password !== '' ? $password : '113010');
}
