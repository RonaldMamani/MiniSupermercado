<?php

class SQLQueries
{
    // Tabelas

    // Autenticação e Usuários
    const AUTH_GET_USER_BY_USERNAME = "SELECT u.id_usuario, u.username, u.senha, p.tipo_perfil AS tipo_perfil_do_perfil, p.id_perfil FROM " . TB_US . " u JOIN " . TB_PF . " p ON u.id_usuario = p.id_usuario WHERE u.username = :username AND u.ativo = TRUE";

    //Estoque
    const ESTOQUE_INSERT = "INSERT INTO " . TB_ES . " (id_produto, data_criacao, ultima_atualizacao) VALUES (:id_produto, NOW(), NOW())";
    const ESTOQUE_UPDATE_LAST_UPDATE = "UPDATE " . TB_ES . " SET ultima_atualizacao = NOW() WHERE id_produto = :id_produto";

    // Produtos
    const PRODUCT_SELECT_ALL = "SELECT p.id_produto, c.id_categoria, c.nome_categoria, p.nome_produto, p.preco, p.quantidade, p.existe FROM " . TB_PR . " p JOIN " . TB_CT . " c ON p.id_categoria = c.id_categoria WHERE p.existe = TRUE ORDER BY p.nome_produto ASC";
    const PRODUCT_SELECT_ALL_BY_ID = "SELECT p.id_produto, c.id_categoria, c.nome_categoria, p.nome_produto, p.preco, p.quantidade, p.existe FROM " . TB_PR . " p JOIN " . TB_CT . " c ON p.id_categoria = c.id_categoria WHERE p.existe = TRUE ORDER BY p.id_produto ASC";
    const PRODUCT_SELECT_BY_ID = "SELECT p.id_produto, c.id_categoria, c.nome_categoria, p.nome_produto, p.preco, p.quantidade, p.existe FROM " . TB_PR . " p JOIN " . TB_CT . " c ON p.id_categoria = c.id_categoria WHERE p.id_produto = :id_produto AND p.existe = TRUE";
    const PRODUCT_CHECK_EXISTS_BY_NAME_AND_PRICE = "SELECT id_produto, nome_produto, preco, quantidade, id_categoria, existe FROM " . TB_PR . " WHERE nome_produto = :nome_produto AND preco = :preco";
    const PRODUCT_REACTIVATE = "UPDATE " . TB_PR . " SET 
                                id_categoria = :id_categoria, 
                                nome_produto = :nome_produto, 
                                preco = :preco, 
                                quantidade = :quantidade, 
                                existe = TRUE 
                              WHERE id_produto = :id_produto";
    const PRODUCT_INSERT = "INSERT INTO " . TB_PR . " 
                                 (id_categoria, nome_produto, preco, quantidade, existe) 
                                 VALUES (:id_categoria, :nome_produto, :preco, :quantidade, TRUE)";
    const PRODUCT_UPDATE = "UPDATE " . TB_PR . " SET id_categoria = :id_categoria, nome_produto = :nome_produto, preco = :preco, quantidade = :quantidade WHERE id_produto = :id_produto";
    const PRODUCT_LOGICAL_DELETE = "UPDATE " . TB_PR . " SET existe = FALSE WHERE id_produto = :id_produto";
    const PRODUCT_UPDATE_QUANTITY = "UPDATE " . TB_PR . " SET quantidade = quantidade - :quantidade_vendida WHERE id_produto = :id_produto AND existe = TRUE";
    const CHECK_PRODUCT = "SELECT quantidade, existe FROM " . TB_PR . " WHERE id_produto = :id_produto";

    // Categorias
    const CATEGORY_SELECT_ALL = "SELECT id_categoria, nome_categoria, descricao FROM " . TB_CT;
    const CATEGORY_INSERT = "INSERT INTO " . TB_CT . " (nome_categoria, descricao) VALUES (:nome_categoria, :descricao)";
    const CATEGORY_UPDATE = "UPDATE " . TB_CT . " SET nome_categoria = :nome_categoria, descricao = :descricao WHERE id_categoria = :id_categoria";
    const CATEGORY_DELETE = "DELETE FROM " . TB_CT . " WHERE id_categoria = :id_categoria";

    // Solicitações
    const SOLICITACAO_CREATE = "INSERT INTO " . TB_SL . " (id_perfil, nome_solicitacao, status, data_solicitacao) VALUES (:id_perfil, :nome_solicitacao, 'pendente', NOW())"; 
    const SOLICITACAO_LIST_ALL = "SELECT s.id_solicitacao, s.id_perfil, p.tipo_perfil AS tipo_perfil, s.nome_solicitacao, s.status, s.data_solicitacao, s.data_aprovacao FROM " . TB_SL . " s JOIN " . TB_PF . " p ON s.id_perfil = p.id_perfil ORDER BY s.data_solicitacao DESC";
    const SOLICITACAO_LIST_ALL_BY_ID = "SELECT s.id_solicitacao, s.id_perfil, p.tipo_perfil AS tipo_perfil, s.nome_solicitacao, s.status, s.data_solicitacao, s.data_aprovacao FROM " . TB_SL . " s JOIN " . TB_PF . " p ON s.id_perfil = p.id_perfil ORDER BY s.id_solicitacao ASC";
    const SOLICITACAO_UPDATE_STATUS = "UPDATE " . TB_SL . " SET status = :status, data_aprovacao = NOW() WHERE id_solicitacao = :id_solicitacao";

    // Permissões
    const PERMISSION_CHECK_ACCESS = "SELECT COUNT(*) FROM " . TB_SL . " WHERE id_perfil = :id_perfil AND nome_solicitacao = :nome_solicitacao AND status = 'aprovado'";
    const PERMISSION_BLOCK_ACCESS = "UPDATE " . TB_SL . " SET status = 'bloqueado' WHERE id_solicitacao = :id_solicitacao";

    // Clientes
    const CLIENTE_SELECT_ALL = "SELECT id_cliente, nome, email, data_cadastro, cpf, rg FROM " . TB_CL . " ORDER BY nome ASC";
    const CLIENTE_SELECT_BY_ID = "SELECT id_cliente, nome, email, data_cadastro, cpf, rg FROM " . TB_CL . " WHERE id_cliente = :id_cliente";


    // Vendas
    const VENDA_INSERT_WITH_CLIENT = "INSERT INTO " . TB_VD . " (id_usuario, id_cliente, data_venda, total) VALUES (:id_usuario, :id_cliente, NOW(), :total)";
    
    // Venda de Produtos
    const VENDA_PRODUTOS_INSERT = "INSERT INTO " . TB_VP . " (id_venda, id_produto, quantidade, preco_unitario, subtotal) VALUES (:id_venda, :id_produto, :quantidade, :preco_unitario, :subtotal)";
}
?>