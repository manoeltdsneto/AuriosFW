<?php
echo "🐎 Iniciando ferramenta de criação (have)...\n";

// Argumentos
$comando = $argv[1] ?? null;
$nome = $argv[2] ?? null;

if (!$comando || !$nome) {
    exit("❗ Uso: php have.php have:controller Nome\n");
}

$base = __DIR__;

switch ($comando) {
    case 'have:controller':
        $file = "$base/app/Controllers/{$nome}.php";
        if (file_exists($file)) {
            echo "⚠️ Já existe: $file\n";
            break;
        }

        // Nome do model deduzido
        $model = strtolower(str_replace('Controller', '', $nome));

        $conteudo = <<<PHP
<?php
namespace App\Controllers;

use Core\\controller;
use App\\Models\\$model;

class $nome extends controller {

    public function index(): void {
        \$this->render('$model/index');
    }

    public function store(): void {
        // lógica para salvar novo registro
    }

    public function edit(\$id): void {
        // lógica para editar
    }

    public function delete(\$id): void {
        // lógica para deletar
    }
}
PHP;

        file_put_contents($file, $conteudo);
        echo "✅ Controller criado: $file\n";
        break;

    case 'have:model':
        $file = "$base/app/Models/{$nome}.php";
        if (file_exists($file)) {
            echo "⚠️ Já existe: $file\n";
            break;
        }

        $table = strtolower($nome) . 's';

        $conteudo = <<<PHP
<?php
namespace App\Models;

use Core\\model;

class $nome extends model {
    protected string \$table = '$table';
}
PHP;

        file_put_contents($file, $conteudo);
        echo "✅ Model criado: $file\n";
        break;

    case 'have:view':
        $path = "$base/app/Views/" . $nome . ".php";
        $dir = dirname($path);
        @mkdir($dir, 0755, true);
        if (file_exists($path)) {
            echo "⚠️ Já existe: $path\n";
            break;
        }

        $conteudo = <<<HTML
<h2>Nova view: $nome</h2>
<form method="post" action="/$nome/store">
    <input type="text" name="campo" placeholder="Nome">
    <button type="submit">Salvar</button>
</form>
HTML;

        file_put_contents($path, $conteudo);
        echo "✅ View criada: $path\n";
        break;

    default:
        echo "❌ Comando desconhecido: $comando\n";
        break;
}
