<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../Connection.php';
require_once __DIR__ . '/../SQLQueries.php';

function listarClientes(): array
{
    try {
        $conn = new PDOConnection();
        $clientes = $conn->run("SELECT id_cliente, nome, email, data_cadastro FROM clientes ORDER BY nome ASC");
        return $clientes ?: [];
    } catch (Exception $e) {
        error_log("Erro ao listar clientes: " . $e->getMessage());
        return [];
    }
}

function obterClientePorId(int $id_cliente): ?array
{
    try {
        $conn = new PDOConnection();
        $params = [':id_cliente' => $id_cliente];
        $result = $conn->run(SQLQueries::CLIENTE_SELECT_BY_ID, $params);
        return $result[0] ?? null;
    } catch (Exception $e) {
        error_log("Erro ao obter cliente por ID ($id_cliente): " . $e->getMessage());
        return null;
    }
}