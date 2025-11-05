<?php
// Configuração de conexão com MySQL (XAMPP padrão)
// Ajuste via variáveis de ambiente caso necessário.

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'cid10';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';

function db_get_pdo(): PDO {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $DB_USER, $DB_PASS, $options);
}

// Verifica rapidamente se a base existe (opcional para mensagens mais claras)
function db_is_ready(PDO $pdo): bool {
    try {
        $pdo->query("SELECT 1 FROM chapters LIMIT 1");
        $pdo->query("SELECT 1 FROM blocks LIMIT 1");
        $pdo->query("SELECT 1 FROM categories LIMIT 1");
        $pdo->query("SELECT 1 FROM subcategories LIMIT 1");
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

?>
