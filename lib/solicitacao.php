<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../Connection.php';
require_once __DIR__ . '/../SQLQueries.php';

function criarSolicitacao(int $id_perfil, string $nome_solicitacao): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':id_perfil' => $id_perfil,
            ':nome_solicitacao' => $nome_solicitacao
        ];
        $conn->run(SQLQueries::SOLICITACAO_CREATE, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao criar solicitação: " . $e->getMessage());
        return false;
    }
}

function listarSolicitacoes(): array
{
    try {
        $conn = new PDOConnection();
        $solicitacoes = $conn->run(SQLQueries::SOLICITACAO_LIST_ALL);
        return $solicitacoes ?: [];
    } catch (Exception $e) {
        error_log("Erro ao listar solicitações: " . $e->getMessage());
        return [];
    }
}

function listarSolicitacoesPorId(): array
{
    try {
        $conn = new PDOConnection();
        $solicitacoes = $conn->run(SQLQueries::SOLICITACAO_LIST_ALL_BY_ID);
        return $solicitacoes ?: [];
    } catch (Exception $e) {
        error_log("Erro ao listar solicitações: " . $e->getMessage());
        return [];
    }
}

function atualizarStatusSolicitacao(int $id_solicitacao, string $status): bool
{
    try {
        $conn = new PDOConnection();
        $params = [
            ':status' => $status,
            ':id_solicitacao' => $id_solicitacao
        ];
        $conn->run(SQLQueries::SOLICITACAO_UPDATE_STATUS, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao atualizar status da solicitação: " . $e->getMessage());
        return false;
    }
}

function bloquearSolicitacao(int $id_solicitacao): bool
{
    try {
        $conn = new PDOConnection();
        $params = [':id_solicitacao' => $id_solicitacao];
        $conn->run(SQLQueries::PERMISSION_BLOCK_ACCESS, $params);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao bloquear solicitação: " . $e->getMessage());
        return false;
    }
}