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
        $params_produto = [
            ':id_categoria' => $id_categoria,
            ':nome_produto' => $nome_produto,
            ':preco' => $preco,
            ':quantidade' => $quantidade
        ];
        $conn->run(SQLQueries::PRODUCT_INSERT, $params_produto);
        $id_produto_inserido = $conn->lastInsertId();

        $params_estoque = [':id_produto' => $id_produto_inserido];
        $conn->run(SQLQueries::ESTOQUE_INSERT, $params_estoque);

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
        $params_produto = [
            ':id_categoria' => $id_categoria,
            ':nome_produto' => $nome_produto,
            ':preco' => $preco,
            ':quantidade' => $quantidade,
            ':id_produto' => $id_produto
        ];
        $conn->run(SQLQueries::PRODUCT_REACTIVATE, $params_produto);

        $params_estoque = [':id_produto' => $id_produto];
        $conn->run(SQLQueries::ESTOQUE_UPDATE_LAST_UPDATE, $params_estoque);

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
        $params_produto = [
            ':id_categoria' => $id_categoria,
            ':nome_produto' => $nome_produto,
            ':preco' => $preco,
            ':quantidade' => $quantidade,
            ':id_produto' => $id_produto
        ];
        $conn->run(SQLQueries::PRODUCT_UPDATE, $params_produto); 

        $params_estoque = [':id_produto' => $id_produto];
        $conn->run(SQLQueries::ESTOQUE_UPDATE_LAST_UPDATE, $params_estoque);

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
        $params_produto = [':id_produto' => $id_produto];
        $conn->run(SQLQueries::PRODUCT_LOGICAL_DELETE, $params_produto);

        $params_estoque = [':id_produto' => $id_produto];
        $conn->run(SQLQueries::ESTOQUE_UPDATE_LAST_UPDATE, $params_estoque);

        return true;
    } catch (Exception $e) {
        error_log("Erro ao deletar produto: " . $e->getMessage());
        return false;
    }
}

function registrarVenda(int $id_usuario, int $id_cliente, array $produtos_vendidos)
{
    $conn = new PDOConnection();
    try {
        $conn->beginTransaction();

        $total_venda = 0;

        $params_venda_principal = [
            ':id_usuario' => $id_usuario,
            ':id_cliente' => $id_cliente,
            ':total' => 0
        ];
        $conn->run(SQLQueries::VENDA_INSERT_WITH_CLIENT, $params_venda_principal);
        $id_venda = $conn->lastInsertId();

        foreach ($produtos_vendidos as $item_venda) {
            $id_produto = $item_venda['id_produto'];
            $quantidade_vendida = $item_venda['quantidade_vendida'];
            $preco_unitario_na_venda = $item_venda['preco_unitario_no_momento_da_venda'];

            $product_results = $conn->run(SQLQueries::CHECK_PRODUCT, [':id_produto' => $id_produto]);
            $current_product = $product_results[0] ?? null; 

            if (!$current_product || $current_product['existe'] == 0) {
                error_log("Tentativa de venda de produto inexistente ou inativo (ID: $id_produto).");
                $conn->rollBack(); 
                return false;
            }

            $current_stock = $current_product['quantidade'];

            if ($current_stock < $quantidade_vendida) {
                error_log("Estoque insuficiente para o produto (ID: $id_produto). Disponível: $current_stock, Tentativa de venda: $quantidade_vendida.");
                $conn->rollBack();
                return false;
            }

            $subtotal_item = $quantidade_vendida * $preco_unitario_na_venda;
            $total_venda += $subtotal_item;

            $params_produto_update = [
                ':quantidade_vendida' => $quantidade_vendida,
                ':id_produto' => $id_produto
            ];
            $conn->run(SQLQueries::PRODUCT_UPDATE_QUANTITY, $params_produto_update); 

            $params_estoque_update = [':id_produto' => $id_produto];
            $conn->run(SQLQueries::ESTOQUE_UPDATE_LAST_UPDATE, $params_estoque_update);

            $params_item_venda = [
                ':id_venda' => $id_venda,
                ':id_produto' => $id_produto,
                ':quantidade' => $quantidade_vendida,
                ':preco_unitario' => $preco_unitario_na_venda,
                ':subtotal' => $subtotal_item
            ];
            $conn->run(SQLQueries::VENDA_PRODUTOS_INSERT, $params_item_venda);
        }

        $conn->run("UPDATE " . TB_VD . " SET total = :total WHERE id_venda = :id_venda", [
            ':total' => $total_venda,
            ':id_venda' => $id_venda
        ]);

        $conn->commit();
        return ['id_venda' => $id_venda, 'total_venda' => $total_venda];

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Erro ao registrar venda completa: " . $e->getMessage());
        return false; 
    }
}