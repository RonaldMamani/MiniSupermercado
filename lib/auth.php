<?php

require_once __DIR__ . '/../Connection.php';
require_once __DIR__ . '/../SQLQueries.php';
require_once __DIR__ . '/../includes/config.php';


function autenticarUser(string $username, string $password): ?array {
    try {
        $conn = new PDOConnection(); 
        // Busca o usuário pelo nome de usuário e verifica a senha
        $users = $conn->run(SQLQueries::AUTH_GET_USER_BY_USERNAME, [':username' => $username]);
        
        $user = $users[0] ?? null;

        if ($user && password_verify($password, $user['senha'])) {
            unset($user['senha']);
            return $user;
        }

        return null;

    } catch (Exception $e) {
        error_log("Erro no autenticarUser (PDOConnection::run): " . $e->getMessage());
    
        return null; 
    }
}

?>