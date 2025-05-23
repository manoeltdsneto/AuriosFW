<?php
echo "==== 🌵 Instalação do Framework MVC (PHP 8.4) ====\n\n";

// 🔒 Bloqueio de execução se já instalado
$lockFile = __DIR__ . '/install.lock';
if (file_exists($lockFile)) {
    exit("⚠️ Instalação já concluída. Remova install.lock para reinstalar.\n");
}

// ✅ Verificação de versão do PHP
$phpOk = version_compare(PHP_VERSION, '8.4.0', '>=');
echo $phpOk ? "✅ PHP " . PHP_VERSION . " (OK)\n" : "❌ PHP " . PHP_VERSION . " (mínimo: 8.4)\n";

if (!$phpOk) exit("⛔ Atualize seu PHP para 8.4 ou superior.\n");

// ✅ Teste de conexão com banco
$config = require __DIR__ . '/config/config.php';
$db = $config['db'];

try {
    $dsn = "{$db['driver']}:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['username'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Banco de dados conectado com sucesso!\n";
} catch (PDOException $e) {
    echo "❌ Erro na conexão com o banco: " . $e->getMessage() . "\n";
    exit("⛔ Corrija as configurações em config/config.php\n");
}

// ✅ Criação da pasta de upload
$uploadDir = $config['upload_dir'];

if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Pasta 'uploads' criada em: $uploadDir\n";
    } else {
        echo "❌ Erro ao criar a pasta: $uploadDir\n";
    }
} else {
    echo "✅ Pasta de upload já existe: $uploadDir\n";
}

// ✅ Permissões
if (is_writable($uploadDir)) {
    echo "✅ Permissões OK para upload\n";
} else {
    echo "❌ Pasta não tem permissão de escrita: $uploadDir\n";
}

// ✅ Scanner de classes (inline)
function scanDirectory($dir)
{
    $files = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $files = array_merge($files, scanDirectory($path));
        } elseif (substr($item, -4) === '.php') {
            $files[] = $path;
        }
    }
    return $files;
}

echo "\n📦 Verificando classes...\n";

$base = realpath(__DIR__);
$dirs = [$base . '/core', $base . '/app/Controllers', $base . '/app/Models'];

foreach ($dirs as $dir) {
    foreach (scanDirectory($dir) as $file) {
        $contents = file_get_contents($file);
        $expectedClass = pathinfo($file, PATHINFO_FILENAME);
        if (!preg_match("/class\s+$expectedClass\b/", $contents)) {
            echo "❌ ERRO: Arquivo $file não contém a classe '$expectedClass'\n";
        } else {
            echo "✅ OK: $expectedClass\n";
        }
    }
}

// ✅ Gera arquivo de lock
file_put_contents($lockFile, "# Gerado automaticamente\nINSTALLED=1\n");
echo "\n🔐 install.lock criado para bloquear reinstalação.\n";

// ✅ Finalização
echo "\n==== ✅ Instalação finalizada ====\n";
echo "Acesse o sistema em: " . $config['base_url'] . "\n";
