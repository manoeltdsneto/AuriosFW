<?php
namespace Core;

/**
 * Classe de roteamento simples e amigável.
 * Responsável por direcionar requisições para o controlador correto.
 */
class Router {
    /**
     * Processa a URL atual e despacha para o controlador correspondente.
     */
    public function dispatch(): void {
        // Captura a URI da requisição e remove a query string
        $url = $_GET['url'] ?? '';
        $url = trim($url, '/');

        // Divide a URL em partes
        $segments = explode('/', $url);

        // Define controlador padrão (caso vazio)
        $controller = ucfirst($segments[0] ?? 'Home') . 'Controller';

        // Define ação padrão (caso não especificada)
        $action = $segments[1] ?? 'index';

        // Define os parâmetros adicionais
        $params = array_slice($segments, 2);

        // Caminho completo do controlador
        $controllerPath = "\\App\\Controllers\\$controller";

        // Verifica se o controlador existe
        if (class_exists($controllerPath)) {
            $instance = new $controllerPath();

            // Verifica se o método (ação) existe no controlador
            if (method_exists($instance, $action)) {
                // Chama o método, passando os parâmetros
                call_user_func_array([$instance, $action], $params);
                return;
            }
        }

        // Se não encontrado, exibe erro básico
        http_response_code(404);
        echo "Página não encontrada.";
    }
}
