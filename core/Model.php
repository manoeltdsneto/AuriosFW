<?php

namespace Core;

use PDO;
use Core\Database;

/**
 * Classe abstrata para Modelos.
 * Implementa operações CRUD genéricas com base no nome da tabela.
 */
abstract class Model
{
    // Cada modelo deve informar a tabela correspondente no banco
    protected string $table;

    // Nome da chave primária (por padrão 'id')
    protected string $primaryKey = 'id';

    // Instância de PDO para acesso ao banco
    protected PDO $db;

    /**
     * Construtor: obtém conexão ativa com o banco de dados
     */
    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Busca todos os registros da tabela.
     *
     * @return array
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um único registro pela chave primária.
     *
     * @param mixed $id
     * @return array|null
     */
    public function find($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Insere um novo registro na tabela.
     *
     * @param array $data Dados no formato ['coluna' => 'valor']
     * @return int ID do novo registro
     */
    public function create(array $data): int
    {
        // Gera campos e placeholders dinamicamente
        $fields = array_keys($data);
        $columns = implode(',', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        // Faz bind seguro de todos os valores
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        // Retorna o ID do novo registro
        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza um registro existente.
     *
     * @param mixed $id ID do registro
     * @param array $data Dados a atualizar
     * @return bool
     */
    public function update($id, array $data): bool
    {
        // Monta os pares campo=:campo
        $fields = array_keys($data);
        $assignments = implode(', ', array_map(fn($f) => "$f = :$f", $fields));

        $sql = "UPDATE {$this->table} SET $assignments WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);

        // Faz bind dos dados
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // Adiciona o ID
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    /**
     * Exclui um registro da tabela.
     *
     * @param mixed $id
     * @return bool
     */
    public function delete($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
