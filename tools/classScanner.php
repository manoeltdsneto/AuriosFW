<?php
/**
 * Scanner de classes para validar estrutura de arquivos do framework
 */

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

// Caminho base do projeto (ajustar conforme necessário)
$base = realpath(__DIR__ . '/../');

// Pastas a validar
$dirs = [$base . '/core', $base . '/app/Controllers', $base . '/app/Models'];

echo "📦 Verificando classes...\n\n";

foreach ($dirs as $dir) {
    foreach (scanDirectory($dir) as $file) {
        $contents = file_get_contents($file);
        $expectedClass = pathinfo($file, PATHINFO_FILENAME);

        // Procura o nome da classe no conteúdo
        if (!preg_match("/class\s+$expectedClass\b/", $contents)) {
            echo "❌ ERRO: Arquivo $file não possui a classe '$expectedClass'\n";
        } else {
            echo "✅ OK: $expectedClass em " . basename($file) . "\n";
        }
    }
}

echo "\n🧪 Verificação concluída!\n";
