<?php

session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/Connection.php';    
require_once __DIR__ . '/SQLQueries.php';    
require_once __DIR__ . '/lib/auth.php';

$error_message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    $user_data = autenticarUser($username, $password);

    if ($user_data) {
        $_SESSION['user_id'] = $user_data['id_usuario'];
        $_SESSION['username'] = $user_data['username'];
        
        $_SESSION['tipo_perfil'] = $user_data['tipo_perfil_do_perfil'] ?? $user_data['tipo_perfil'] ?? null;
        
        $_SESSION['id_perfil'] = $user_data['id_perfil'];
        
        // Redireciona sempre para o dashboard, que fará a lógica de painel por perfil
        header('Location: views/dashboard.php'); 
        exit();
    } else {
        $error_message = 'Usuário ou senha inválidos.';
    }
}

if (isset($_SESSION['user_id'])) {
    header('Location: views/dashboard.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body class="bg-primary d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Login</h2>
                        <?php if (isset($error_message) && !empty($error_message)): ?>
                            <div class="alert alert-danger text-center" role="alert">
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuário:</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha:</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>