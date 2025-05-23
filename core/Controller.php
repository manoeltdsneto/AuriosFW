<?php

namespace Core;

/**
 * Classe base para todos os controladores da aplicação.
 * Oferece métodos utilitários para carregar views e passar dados.
 */
class Controller
{
    /**
     * Renderiza uma view.
     *
     * @param string $view Nome da view (ex: 'user/list')
     * @param array $data Dados a serem passados para a view
     */
    protected function render(string $view, array $data = []): void
    {
        // Extrai as variáveis do array para uso na view
        extract($data);

        // Caminho completo do arquivo da view
        $file = __DIR__ . '/../app/Views/' . $view . '.php';

        // Verifica se a view existe
        if (file_exists($file)) {
            require_once $file;
        } else {
            // Em produção, é melhor redirecionar ou logar, mas aqui mostramos um erro básico
            http_response_code(500);
            echo "Erro: View '{$view}' não encontrada.";
        }
    }

    /**
     * Redireciona para outra URL interna.
     *
     * @param string $path Caminho relativo (ex: 'user/index')
     */
    protected function redirect(string $path): void
    {
        // Usa a base URL definida na configuração
        $config = require __DIR__ . '/../config/config.php';
        $baseUrl = rtrim($config['base_url'], '/');

        // Faz o redirecionamento com header
        header("Location: {$baseUrl}/{$path}");
        exit;
    }
}
