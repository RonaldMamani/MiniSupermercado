<?php

require_once __DIR__ . '/../../lib/produto.php';
require_once __DIR__ . '/../../lib/solicitacao.php';
require_once __DIR__ . '/../../lib/categoria.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function verificarPermissaoEdicaoEstoque(): bool {
    $solicitacoes = listarSolicitacoes();
    foreach ($solicitacoes as $solicitacao) {
        if ($solicitacao['nome_solicitacao'] == 'Permissão para Editar Produtos' &&
            $solicitacao['status'] == 'aprovado') {
            return true;
        }
    }
    return false;
}

$pode_editar = verificarPermissaoEdicaoEstoque();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'adicionar_produto') {
        if ($pode_editar) {
            $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
            $nome_produto = filter_input(INPUT_POST, 'nome_produto', FILTER_SANITIZE_STRING);
            $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

            if ($id_categoria && $nome_produto && $preco !== false && $quantidade !== false && $quantidade >= 0) {
                if (inserirProduto($id_categoria, $nome_produto, $preco, $quantidade)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto "' . htmlspecialchars($nome_produto) . '" adicionado com sucesso!'];
                } else {
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao adicionar produto.'];
                }
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Dados de produto inválidos.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Você não tem permissão para adicionar produtos.'];
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'adicionar_categoria') {
        if ($pode_editar) {
            $nome_categoria = filter_input(INPUT_POST, 'nome_nova_categoria', FILTER_SANITIZE_STRING);
            $descricao_categoria = filter_input(INPUT_POST, 'descricao_nova_categoria', FILTER_SANITIZE_STRING);

            if ($nome_categoria) {
                if (inserirCategoria($nome_categoria, $descricao_categoria)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Categoria "' . htmlspecialchars($nome_categoria) . '" adicionada com sucesso!'];
                } else {
                    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao adicionar categoria.'];
                }
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Nome da categoria não pode ser vazio.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Você não tem permissão para adicionar categorias.'];
        }
    }
    // Redireciona para evitar reenvio do formulário
    header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=estoque');
    exit();
}

// Processamento da exclusão (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'deletar_produto') {
    if ($pode_editar) {
        $id_produto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id_produto) {
            if (deletarProdutoLogico($id_produto)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto deletado com sucesso!'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao deletar produto.'];
            }
        } else {
             $_SESSION['message'] = ['type' => 'danger', 'text' => 'ID do produto inválido para exclusão.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Você não tem permissão para deletar produtos.'];
    }
    // Redireciona para evitar reenvio da requisição GET
    header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=estoque');
    exit();
}

// Lendo a mensagem da sessão e limpando-a
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$produtos = listarProdutos();
$categorias = listarCategorias();
?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h3 class="card-title mb-0"><i class="bi bi-boxes me-2"></i>Painel do Estoque</h3>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($pode_editar): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>Permissão para adicionar/editar/deletar concedida pelo Financeiro.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h4 class="mb-0">Adicionar Nova Categoria</h4>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="adicionar_categoria">
                                    <div class="mb-3">
                                        <label for="nome_nova_categoria" class="form-label">Nome da Categoria:</label>
                                        <input type="text" class="form-control" id="nome_nova_categoria" name="nome_nova_categoria" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descricao_nova_categoria" class="form-label">Descrição (Opcional):</label>
                                        <input type="text" class="form-control" id="descricao_nova_categoria" name="descricao_nova_categoria">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-tag-fill me-2"></i>Adicionar Categoria
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h4 class="mb-0">Adicionar Novo Produto</h4>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="adicionar_produto">
                                    <div class="mb-3">
                                        <label for="id_categoria" class="form-label">Categoria:</label>
                                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                                            <?php if (!empty($categorias)): ?>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?= htmlspecialchars($categoria['id_categoria']) ?>">
                                                        <?= htmlspecialchars($categoria['nome_categoria']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">Nenhuma categoria encontrada</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nome_produto" class="form-label">Nome do Produto:</label>
                                        <input type="text" class="form-control" id="nome_produto" name="nome_produto" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="preco" class="form-label">Preço:</label>
                                        <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="quantidade" class="form-label">Quantidade:</label>
                                        <input type="number" class="form-control" id="quantidade" name="quantidade" min="0" required>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-plus-circle-fill me-2"></i>Adicionar Produto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> 
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-hourglass-split me-2"></i>Aguardando permissão do Financeiro para adicionar/editar/deletar.
                    </div>
                <?php endif; ?>

            <h4 class="mb-3 mt-4">Lista de Produtos</h4>
            <?php if (!empty($produtos)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="col">Produto</th>
                                <th class="col">Categoria</th>
                                <th class="col">Preço</th>
                                <th class="col">Estoque</th>
                                <?php if ($pode_editar): ?>
                                    <th class="col" class="text-center">Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                                    <td><?= htmlspecialchars($produto['nome_categoria']) ?></td>
                                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?= ($produto['quantidade'] > 15) ? 'success' : (($produto['quantidade'] > 5) ? 'warning' : 'danger') ?>">
                                            <?= htmlspecialchars($produto['quantidade']) ?>
                                        </span>
                                    </td>
                                    <?php if ($pode_editar): ?>
                                        <td class="text-center">
                                            <a href="painel/editar_produto.php?id=<?= $produto['id_produto'] ?>" class="btn btn-sm btn-outline-secondary me-2">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                            <a href="?panel=estoque&action=deletar_produto&id=<?= $produto['id_produto'] ?>"
                                               onclick="return confirm('Tem certeza que deseja desativar este produto?');"
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Deletar
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    Nenhum produto cadastrado no estoque.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>