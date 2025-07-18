<?php

date_default_timezone_set('America/Sao_Paulo');

$Globals = [];

$envFile = __DIR__ . '/../.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || empty(trim($line))) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);

        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = trim($value, '"');
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    $envKeys = [
        'DB_SEC_HOST',
        'DB_SEC_PORT',
        'DB_SEC_USER',
        'DB_SEC_PASS',
        'DB_SEC_SCHEMA'
    ];

    define('TB_US', getenv('TB_US'));
    define('TB_PF', getenv('TB_PF'));
    define('TB_ES', getenv('TB_ES'));
    define('TB_PR', getenv('TB_PR'));
    define('TB_CT', getenv('TB_CT'));
    define('TB_SL', getenv('TB_SL'));
    define('TB_CL', getenv('TB_CL'));
    define('TB_VD', getenv('TB_VD'));
    define('TB_VP', getenv('TB_VP'));

    foreach ($envKeys as $envKey) {
        $envValue = getenv($envKey);
        if ($envValue !== false) {
            $Globals[$envKey] = $envValue;
        } else {
            error_log("Aviso: Variável de ambiente '$envKey' não encontrada no .env ou não carregada.");
        }
    }

} else {
    error_log("Erro: O arquivo .env não foi encontrado em " . $envFile);
    die("Erro de configuração: O arquivo .env é essencial e não foi encontrado.");
}

require_once __DIR__ . '/../Connection.php';
require_once __DIR__ . '/../SQLQueries.php';


?>