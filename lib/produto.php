<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../Connection.php';
require_once __DIR__ . '/../SQLQueries.php';

function listarProdutos(): array
{
    try {
        $conn = new PDOConnection();
        $produtos = $conn->run(SQLQueries::PRODUCT_SELECT_ALL);
        return $produtos ?: [];
    } catch (Exception $e) {
        error_log("Erro ao listar produtos: " . $e->getMessage());
        return [];
    }
}

function listarProdutosPorId(): array
{
    try {
        $conn = new PDOConnection();
        $produtos = $conn->run(SQLQueries::PRODUCT_SELECT_ALL_BY_ID);
        return $produtos ?: [];
    } catch (Exception $e) {
        error_log("Erro ao listar produtos: " . $e->getMessage());
        return [];
    }
}

function obterProdutoPorId(int $id_produto): ?array
{
    try {
        $conn = new PDOConnection();
        $params = [':id_produto' => $id_produto];
        $produto = $conn->run(SQLQueries::PRODUCT_SELECT_BY_ID, $params);
        return $produto[0] ?? null;
    } catch (Exception $e) {
        error_log("Erro ao obter produto por ID: " . $e->getMessage());
        return null;
    }
}

function inserirProduto(int $id_categoria, string $nome_produto, float $preco, int $quantidade): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':id_categoria' => $id_categoria,
            ':nome_produto' => $nome_produto,
            ':preco' => $preco,
            ':quantidade' => $quantidade
        ];
        $conn->run(SQLQueries::PRODUCT_INSERT_FULL, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao inserir novo produto: " . $e->getMessage());
        return false;
    }
}

function reativarProduto(int $id_produto, int $id_categoria, string $nome_produto, float $preco, int $quantidade): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':id_categoria' => $id_categoria,
            ':nome_produto' => $nome_produto,
            ':preco' => $preco,
            ':quantidade' => $quantidade,
            ':id_produto' => $id_produto
        ];
        $conn->run(SQLQueries::PRODUCT_REACTIVATE, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao reativar produto: " . $e->getMessage());
        return false;
    }
}

function verificarProdutoExiste(string $nome_produto, float $preco): ?array
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':nome_produto' => $nome_produto,
            ':preco' => $preco
        ];
        $result = $conn->run(SQLQueries::PRODUCT_CHECK_EXISTS_BY_NAME_AND_PRICE, $params);
        return $result[0] ?? null;
    } catch (Exception $e) {
        error_log("Erro ao verificar existência de produto: " . $e->getMessage());
        return null;
    }
}

function editarProduto(int $id_produto, int $id_categoria, string $nome_produto, float $preco, int $quantidade): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':id_categoria' => $id_categoria,
            ':nome_produto' => $nome_produto,
            ':preco' => $preco,
            ':quantidade' => $quantidade,
            ':id_produto' => $id_produto
        ];  
        $conn->run(SQLQueries::PRODUCT_UPDATE, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao editar produto: " . $e->getMessage());
        return false;
    }
}

function deletarProdutoLogico(int $id_produto): bool
{
    try {
        $conn = new PDOConnection();
        $params = [':id_produto' => $id_produto];
        $conn->run(SQLQueries::PRODUCT_LOGICAL_DELETE, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao deletar produto: " . $e->getMessage());
        return false;
    }
}

function venderProduto(int $id_produto, int $quantidade_vendida): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':quantidade_vendida_1' => $quantidade_vendida,
            ':id_produto' => $id_produto,
            ':quantidade_vendida_2' => $quantidade_vendida
        ];
        $conn->run(SQLQueries::PRODUCT_UPDATE_QUANTITY, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao vender produto: " . $e->getMessage());
        return false;
    }
}