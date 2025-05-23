<?php
require_once __DIR__ . '/../config/config.php';

session_start();
$config = require __DIR__ . '/../config/config.php';
echo "<h1>Status do Sistema</h1>";
echo "<p>PHP Versão: " . PHP_VERSION . "</p>";
echo "<p>Base URL: {$config['base_url']}</p>";

try {
    $db = $config['db'];
    $dsn = "{$db['driver']}:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['username'], $db['password']);
    echo "<p style='color:green;'>Conexão com o banco: OK</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro ao conectar com o banco: {$e->getMessage()}</p>";
}

if (!is_writable($config['upload_dir'])) {
    echo "<p style='color:red;'>Uploads: Sem permissão de escrita</p>";
} else {
    echo "<p style='color:green;'>Uploads: OK</p>";
}
