<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Classe responsável por fornecer a conexão com o banco de dados.
 * Utiliza o padrão singleton para garantir uma única instância de PDO.
 */
class Database {
    private static ?PDO $pdo = null;

    /**
     * Retorna uma instância PDO conectada ao banco.
     * Se já existir uma conexão, reutiliza a mesma.
     *
     * @return PDO
     * @throws PDOException
     */
    public static function connection(): PDO {
        // Verifica se já existe uma instância criada
        if (!self::$pdo) {
            // Carrega configurações
            $config = require __DIR__ . '/../config/config.php';
            $db = $config['db'];

            // Monta o DSN com base no driver definido
            $dsn = "{$db['driver']}:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";

            // Cria nova instância PDO
            self::$pdo = new PDO($dsn, $db['username'], $db['password']);

            // Define o modo de erro como exceção para melhor controle
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // Retorna a instância ativa
        return self::$pdo;
    }
}
