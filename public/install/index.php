<?php
function checkRequirement(string $label, bool $ok, string $tip = ''): string
{
    $icon = $ok ? '✅' : '❌';
    $class = $ok ? 'text-success' : 'text-danger';
    $tipHtml = $ok ? '' : "<br><small class='text-muted'>$tip</small>";
    return "<tr><td>$label</td><td class='$class'>$icon $tipHtml</td></tr>";
}

$configPath = __DIR__ . '/../../config/config.php';
$examplePath = __DIR__ . '/../../config/config.example.php';
$uploadDir = __DIR__ . '/../../storage/uploads';

$phpVersionOk = version_compare(PHP_VERSION, '8.4.0', ">=");
$routerExists = file_exists(__DIR__ . '/../../core/router.php');
$autoloadExists = file_exists(__DIR__ . '/../../core/helpers.php');
$os = strtoupper(PHP_OS);
$skipWritableCheck = str_starts_with($os, 'WIN');
$writableUpload = $skipWritableCheck || (is_dir($uploadDir) && is_writable($uploadDir));

$tipUpload = match (true) {
    str_starts_with($os, 'WIN') => "Clique com o botão direito na pasta 'storage/uploads', vá em Propriedades → Segurança e dê permissão total para 'IUSR' ou 'Todos'.",
    str_starts_with($os, 'LINUX'), str_starts_with($os, 'DARWIN') => "Execute: <code>chmod -R 755 storage/uploads</code> ou <code>chown -R www-data:www-data</code>",
    default => "Verifique permissões no sistema de arquivos."
};

$requisitosOk = $phpVersionOk && $writableUpload && $routerExists && $autoloadExists;
$configExists = file_exists($configPath);
$configCriadoAgora = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_config'])) {
    $host = $_POST['host'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $dbname = $_POST['dbname'];
    $charset = $_POST['charset'];
    $driver = $_POST['driver'];

    try {
        $pdo = new PDO("$driver:host=$host;charset=$charset", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET $charset COLLATE {$charset}_general_ci;");

        $template = file_get_contents($examplePath);
        $template = str_replace(
            ['DB_DRIVER', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'],
            [$driver, $host, $dbname, $user, $pass, $charset],
            $template
        );

        file_put_contents($configPath, $template);
        $configCriadoAgora = true;
        header('Location: index.php?instalado=1');
        exit;
    } catch (Throwable $e) {
        echo json_encode(['erro' => $e->getMessage()]);
        exit;
    }
}

$dbConnectionOk = false;
$dbErrorMessage = '';
if ($configExists) {
    try {
        $cfg = require $configPath;
        $db = $cfg['db'];
        $dsnBase = "{$db['driver']}:host={$db['host']};charset={$db['charset']}";
        $pdo = new PDO($dsnBase, $db['username'], $db['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("USE `{$db['dbname']}`;");
        $pdo->query('SELECT 1');
        $dbConnectionOk = true;
    } catch (Throwable $e) {
        $dbConnectionOk = false;
        $dbErrorMessage = "Verifique se o host, usuário, senha e nome do banco estão corretos.<br><small class='text-muted'>Erro: " . $e->getMessage() . "</small>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Instalador do Framework</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">🚀 Instalador do Framework</h1>
    <div class="progress mb-4">
        <div class="progress-bar bg-success" style="width: 33%;" id="progress-bar">Etapa 1/3</div>
    </div>

    <!-- Etapa 1: Requisitos -->
    <div id="etapa-1">
        <h2>✅ Etapa 1: Requisitos do sistema</h2>
        <table class="table table-bordered">
            <thead class="table-light">
            <tr><th>Requisito</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?= checkRequirement('PHP >= 8.4', $phpVersionOk) ?>
            <?= checkRequirement('Pasta storage/uploads acessível', $writableUpload, $tipUpload) ?>
            <?= checkRequirement('Arquivo core/router.php', $routerExists) ?>
            <?= checkRequirement('Arquivo core/helpers.php', $autoloadExists) ?>
            </tbody>
        </table>

        <?php if (!$requisitosOk): ?>
            <div class="alert alert-warning">Corrija os requisitos acima para continuar.</div>
        <?php else: ?>
            <button id="btn-etapa-1" class="btn btn-primary">Continuar</button>
        <?php endif; ?>
    </div>

    <!-- Etapa 2: Dados do banco -->
    <div id="etapa-2" style="display:none;">
        <h2 class="mt-5">⚙️ Etapa 2: Criar banco e config.php</h2>
        <form method="post">
            <input type="hidden" name="criar_config" value="1">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Driver</label>
                    <select name="driver" class="form-select" required>
                        <option value="mysql">MySQL</option>
                        <option value="pgsql">PostgreSQL</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Host</label>
                    <input name="host" id="hostField" class="form-control" value="127.0.0.1" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Nome do Banco (novo)</label>
                    <input name="dbname" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Usuário</label>
                    <input name="user" class="form-control" value="root" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Senha</label>
                    <input type="password" name="pass" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Charset</label>
                    <select name="charset" class="form-select">
                        <option value="utf8mb4" selected>utf8mb4</option>
                        <option value="utf8">utf8</option>
                        <option value="latin1">latin1</option>
                        <option value="ASCII">ASCII</option>
                        <option value="ucs2">ucs2</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-success">Criar banco e config.php</button>
        </form>
    </div>

    <!-- Etapa 3: Conexão -->
    <div id="etapa-3" style="display:<?= $dbConnectionOk ? 'block' : 'none' ?>">
        <h2 class="mt-5">📡 Etapa 3: Conexão com Banco</h2>
        <table class="table table-bordered">
            <tbody>
            <?= checkRequirement('Conexão com banco', $dbConnectionOk, $dbErrorMessage) ?>
            </tbody>
        </table>

        <?php if ($dbConnectionOk): ?>
            <div class="alert alert-success">✅ Tudo pronto! O sistema está instalado.</div>
            <a href="/" class="btn btn-primary">Acessar aplicação</a>
            <a href="/../build.php" class="btn btn-outline-secondary ms-2">Executar build</a>
        <?php endif; ?>
    </div>
</div>

<script>
    $(function () {
        $('#btn-etapa-1').click(function () {
            $('#etapa-1').hide();
            $('#etapa-2').show();
            $('#progress-bar').css('width', '66%').text('Etapa 2/3');
        });

        // Detecta host automaticamente (localhost/ip)
        const userHost = location.hostname;
        if (userHost) $('#hostField').val(userHost);
    });
</script>
</body>
</html>
