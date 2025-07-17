<?php
session_start();

require_once __DIR__ . '/../../lib/produto.php';
require_once __DIR__ . '/../../lib/categoria.php';
require_once __DIR__ . '/../../lib/solicitacao.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_perfil'] != 'estoque' && $_SESSION['tipo_perfil'] != 'admin')) {
    header('Location: ../../login.php');
    exit();
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

if (!$pode_editar && $_SESSION['tipo_perfil'] != 'admin') {
    // Armazena uma mensagem na sessão antes de redirecionar
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Você não tem permissão para editar produtos.'];
    header('Location: ../dashboard.php?painel=estoque');
    exit();
}

$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}


$produto_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$produto = null;

if (!$produto_id) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'ID do produto inválido.'];
    header('Location: ../dashboard.php?painel=estoque'); 
    exit();
} else {
    $produto = obterProdutoPorId($produto_id);
    if (!$produto) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Produto não encontrado.'];
        header('Location: ../dashboard.php?painel=estoque');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_produto') {

    if ($pode_editar || $_SESSION['tipo_perfil'] == 'admin') {
        $id_produto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
        $nome_produto = filter_input(INPUT_POST, 'nome_produto');
        $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

        if ($id_produto && $id_categoria && $nome_produto && $preco !== false && $quantidade !== false && $quantidade >= 0) {
            if (editarProduto($id_produto, $id_categoria, $nome_produto, $preco, $quantidade)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto "' . htmlspecialchars($nome_produto) . '" atualizado com sucesso!'];
                // Atualiza a variável $produto para refletir as mudanças no formulário
                $produto = obterProdutoPorId($id_produto);
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao atualizar produto.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Dados de produto inválidos para atualização.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Você não tem permissão para editar produtos.'];
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $produto_id);
    exit();
}

$categorias = listarCategorias();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Sistema Supermercado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg p-4">
                    <div class="card-header bg-primary text-white text-center">
                        <h1 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Editar Produto
                        </h1>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($message['text']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($produto): ?>
                            <?php if ($pode_editar || $_SESSION['tipo_perfil'] == 'admin'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="editar_produto">
                                    <input type="hidden" name="id_produto" value="<?= htmlspecialchars($produto['id_produto']) ?>">

                                    <div class="mb-3">
                                        <label for="id_categoria" class="form-label">Categoria:</label>
                                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                                            <?php if (!empty($categorias)): ?>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?= htmlspecialchars($categoria['id_categoria']) ?>"
                                                        <?= ($categoria['id_categoria'] == $produto['id_categoria']) ? 'selected' : '' ?>>
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
                                        <input type="text" class="form-control" id="nome_produto" name="nome_produto" value="<?= htmlspecialchars($produto['nome_produto']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="preco" class="form-label">Preço:</label>
                                        <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0" value="<?= htmlspecialchars($produto['preco']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="quantidade" class="form-label">Quantidade:</label>
                                        <input type="number" class="form-control" id="quantidade" name="quantidade" min="0" value="<?= htmlspecialchars($produto['quantidade']) ?>" required>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Atualizar Produto
                                        </button>
                                        <a href="../dashboard.php?painel=estoque" class="btn btn-primary btn-lg">
                                            <i class="bi bi-arrow-left-circle me-2"></i>Voltar ao Painel de Estoque
                                        </a>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-x-circle-fill me-2"></i>Você não tem permissão para editar este produto.
                                </div>
                                <div class="d-grid gap-2 mt-4">
                                    <a href="../dashboard.php?painel=estoque" class="btn btn-secondary btn-lg">
                                        <i class="bi bi-arrow-left-circle me-2"></i>Voltar ao Painel de Estoque
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Produto não encontrado ou ID inválido.
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <a href="../dashboard.php?painel=estoque" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-arrow-left-circle me-2"></i>Voltar ao Painel de Estoque
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>