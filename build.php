<?php
echo "🚀 Iniciando build de assets...\n";

// Diretórios
$base = __DIR__;
$scssInputDir = $base . '/resources/scss';
$assetsInputDir = $base . '/resources/assets';
$cssOutputDir = $base . '/public/css';
$jsOutputDir = $base . '/public/js';

@mkdir($cssOutputDir, 0755, true);
@mkdir($jsOutputDir, 0755, true);

// 🔧 Minificador simples
function minifyAndSave(string $inputFile, string $outputFile): void {
    $raw = file_get_contents($inputFile);
    $min = preg_replace('!/\*.*?\*/!s', '', $raw);
    $min = preg_replace('/\n\s*\n/', "\n", $min);
    $min = preg_replace('/[\r\n\t ]+/', ' ', $min);
    $min = preg_replace('/ ?([,:;{}]) ?/', '$1', $min);
    file_put_contents($outputFile, $min);
    echo "✅ Minificado: " . basename($outputFile) . "\n";
}

// 🧠 Verifica se o SCSS é utilizável
function isSassAvailable(): bool {
    exec('sass --version 2>&1', $out, $code);
    return $code === 0;
}

// 🔨 Compilador SCSS
function compileScss(string $input, string $output): void {
    $cmd = "sass --style=compressed " . escapeshellarg($input) . " " . escapeshellarg($output);
    exec($cmd, $out, $code);
    echo $code === 0 ? "✅ SCSS compilado: " . basename($output) . "\n" : "❌ SCSS ERRO: " . implode("\n", $out) . "\n";
}

// ▶️ SCSS facultativo
if (is_dir($scssInputDir) && isSassAvailable()) {
    foreach (glob("$scssInputDir/*.scss") as $file) {
        $name = pathinfo($file, PATHINFO_FILENAME);
        compileScss($file, "$cssOutputDir/{$name}.min.css");
    }
} else {
    echo "ℹ️ SCSS não encontrado ou 'sass' não instalado. Ignorando...\n";
}

// ▶️ Minificação de arquivos livres (sempre)
foreach (glob("$assetsInputDir/*.css") as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    minifyAndSave($file, "$cssOutputDir/{$name}.min.css");
}

foreach (glob("$assetsInputDir/*.js") as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    minifyAndSave($file, "$jsOutputDir/{$name}.min.js");
}

echo "\n🏁 Build finalizado com sucesso! Use addCss('arquivo.min') ou addJs('arquivo.min') nas views.\n";
