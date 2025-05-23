<?php
namespace Core;

/**
 * Classe responsável por upload, listagem e exclusão de arquivos.
 * Foco em segurança e organização em diretórios configuráveis.
 */
class fileManager
{
    protected string $uploadDir;
    protected array $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    /**
     * Construtor
     *
     * @param string|null $uploadDir Caminho completo do diretório de uploads
     */
    public function __construct(?string $uploadDir = null)
    {
        // Usa diretório do config se não for passado
        $config = require __DIR__ . '/../config/config.php';
        $this->uploadDir = $uploadDir ?? $config['upload_dir'];

        // Garante que o diretório existe
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Realiza o upload de um arquivo
     *
     * @param array $file Arquivo do $_FILES (ex: $_FILES['arquivo'])
     * @return string|null Nome salvo ou null se falhar
     */
    public function upload(array $file): ?string
    {
        // Verifica se houve erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Verifica tipo permitido
        if (!in_array($file['type'], $this->allowedTypes)) {
            return null;
        }

        // Gera nome único e mantém a extensão original
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = uniqid('file_', true) . '.' . strtolower($ext);

        $destination = $this->uploadDir . DIRECTORY_SEPARATOR . $safeName;

        // Move arquivo para o destino final
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $safeName;
        }

        return null;
    }

    /**
     * Lista os arquivos no diretório
     *
     * @return array
     */
    public function list(): array
    {
        $files = scandir($this->uploadDir);
        return array_values(array_filter($files, function ($file) {
            return is_file($this->uploadDir . DIRECTORY_SEPARATOR . $file);
        }));
    }

    /**
     * Exclui um arquivo do diretório
     *
     * @param string $filename Nome do arquivo salvo
     * @return bool
     */
    public function delete(string $filename): bool
    {
        // Impede tentativas de path traversal
        if (preg_match('/\.\.|[\/\\\\]/', $filename)) {
            return false;
        }

        $file = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;

        return file_exists($file) && unlink($file);
    }

    /**
     * Retorna a URL pública de um arquivo
     *
     * @param string $filename
     * @return string
     */
    public function getUrl(string $filename): string
    {
        $config = require __DIR__ . '/../config/config.php';
        return $config['base_url'] . '/uploads/' . rawurlencode($filename);
    }
}
