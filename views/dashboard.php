<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$tipo_perfil = $_SESSION['tipo_perfil'];
$id_perfil_logado = $_SESSION['id_perfil'];

$painel = $_GET['painel'] ?? ($tipo_perfil == 'admin' ? 'admin' : $tipo_perfil);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars(ucfirst($tipo_perfil)) ?> - Sistema Supermercado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shop me-2"></i>Sistema Supermercado
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if ($tipo_perfil == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($painel == 'admin') ? 'active' : '' ?>" aria-current="page" href="?painel=admin">
                                <i class="bi bi-speedometer2 me-1"></i> Administrador
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($painel == 'estoque') ? 'active' : '' ?>" href="?painel=estoque">
                                <i class="bi bi-boxes me-1"></i> Estoque
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($painel == 'caixa') ? 'active' : '' ?>" href="?painel=caixa">
                                <i class="bi bi-cash-coin me-1"></i> Caixa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($painel == 'financeiro') ? 'active' : '' ?>" href="?painel=financeiro">
                                <i class="bi bi-wallet-fill me-1"></i> Financeiro
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> Olá, <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><h6 class="dropdown-header">Perfil: <?= htmlspecialchars(ucfirst($tipo_perfil)) ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i> Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    Bem-vindo ao dashboard, <strong><?= htmlspecialchars($_SESSION['username']) ?>!</strong>
                </div>
            </div>
        </div>

        <section class="content mt-3">
            <?php
            // Lógica de inclusão de painéis
            $allowed_painels = [
                'admin' => ['admin'],
                'estoque' => ['estoque', 'admin'],
                'caixa' => ['caixa', 'admin'],
                'financeiro' => ['financeiro', 'admin']
            ];

            if (isset($allowed_painels[$painel]) && in_array($tipo_perfil, $allowed_painels[$painel])) {
                $painel_file = 'painel/' . $painel . '.php';
                if (file_exists($painel_file)) {
                    include $painel_file;
                } else {
                    echo '<div class="alert alert-warning" role="alert"><p>O painel "' . htmlspecialchars($painel) . '" não foi encontrado.</p></div>';
                }
            } else {
                echo '<div class="alert alert-danger" role="alert"><p>Você não tem permissão para acessar este painel ou o painel não existe.</p></div>';
            }
            ?>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>