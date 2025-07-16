<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../Connection.php';
require_once __DIR__ . '/../SQLQueries.php';

function listarCategorias(): array
{
    try {
        $conn = new PDOConnection();
        $categorias = $conn->run(SQLQueries::CATEGORY_SELECT_ALL);
        return $categorias ?: [];
    } catch (PDOException $e) {
        error_log("Erro ao listar categorias: " . $e->getMessage());
        return [];
    }
}

function inserirCategoria(string $nome, string $descricao = null): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':nome_categoria' => $nome,
            ':descricao' => $descricao
        ];
        $conn->run(SQLQueries::CATEGORY_INSERT, $params);
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao inserir categoria: " . $e->getMessage());
        return false;
    }
}
