<?php
// Mostra erros em ambiente de desenvolvimento
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoloader sem Composer
spl_autoload_register(function ($class) {
    // Converte o namespace em caminho de pasta
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Constrói caminho do arquivo
    $file = __DIR__ . '/../' . $path . '.php';

    // Inclui o arquivo se ele existir
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("Autoload: arquivo não encontrado para a classe $class => $file");
    }
});

require_once __DIR__ . '/../core/helpers.php';

// Roteamento básico
use Core\Router;

// Instancia e processa a requisição
$router = new Router();
$router->dispatch();
