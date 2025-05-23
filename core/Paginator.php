<?php

namespace Core;

use PDO;

/**
 * Classe de paginação reutilizável.
 * Permite paginar qualquer tabela com segurança e flexibilidade.
 */
class Paginator
{
    protected PDO $db;         // Instância PDO para consultas
    protected string $table;   // Nome da tabela
    protected int $limit;      // Registros por página
    protected int $page;       // Página atual
    protected string $orderBy; // Campo de ordenação (ex: 'id DESC')

    public int $totalRecords;  // Total de registros encontrados
    public int $totalPages;    // Total de páginas

    /**
     * Construtor da paginação
     *
     * @param string $table Nome da tabela no banco
     * @param int $limit Quantidade de itens por página
     * @param int $page Página atual
     * @param string $orderBy Campo de ordenação
     */
    public function __construct(string $table, int $limit = 10, int $page = 1, string $orderBy = 'id DESC')
    {
        $this->db = Database::connection();
        $this->table = $table;
        $this->limit = $limit > 0 ? $limit : 10;
        $this->page = $page > 0 ? $page : 1;
        $this->orderBy = $orderBy;

        // Calcula o total de registros e páginas ao instanciar
        $this->totalRecords = $this->countTotal();
        $this->totalPages = (int) ceil($this->totalRecords / $this->limit);
    }

    /**
     * Conta o total de registros da tabela
     *
     * @return int
     */
    protected function countTotal(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna os registros da página atual
     *
     * @return array
     */
    public function getData(): array
    {
        $offset = ($this->page - 1) * $this->limit;

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY {$this->orderBy} LIMIT :limit OFFSET :offset");

        // Define os parâmetros com bind seguro
        $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gera links de paginação HTML (opcional, básico)
     *
     * @param string $baseUrl URL base com placeholder ?page=
     * @return string HTML dos links
     */
    public function renderLinks(string $baseUrl): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav class="pagination"><ul>';

        for ($i = 1; $i <= $this->totalPages; $i++) {
            $class = ($i === $this->page) ? 'active' : '';
            $html .= "<li class='{$class}'><a href='{$baseUrl}?page={$i}'>{$i}</a></li>";
        }

        $html .= '</ul></nav>';
        return $html;
    }
}
