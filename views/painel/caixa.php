<?php

require_once __DIR__ . '/../../lib/produto.php';

// Processamento da venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vender_produto') {
    $id_produto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
    $quantidade_vendida = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

    if ($id_produto && $quantidade_vendida > 0) {
        if (venderProduto($id_produto, $quantidade_vendida)) {
            // Sucesso: usar alertas Bootstrap
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Venda realizada com sucesso!'];
        } else {
            // Erro: usar alertas Bootstrap
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao realizar venda ou quantidade insuficiente.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Dados de venda inválidos.'];
    }
    // Redireciona para evitar reenvio do formulário ao recarregar a página
    header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=caixa');
    exit();
}

$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$produtos = listarProdutos();
?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h3 class="card-title mb-0"><i class="bi bi-cash-coin me-2"></i>Painel do Caixa</h3>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h4 class="mb-3">Lista de Produtos</h4>
            <?php if (!empty($produtos)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="col">Produto</th>
                                <th class="col">Preço</th>
                                <th class="col">Estoque</th>
                                <th class="col" class="text-center">Vender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?= ($produto['quantidade'] > 15) ? 'success' : (($produto['quantidade'] > 5) ? 'warning' : 'danger') ?>">
                                            <?= htmlspecialchars($produto['quantidade']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" action="" class="d-flex justify-content-center align-items-center">
                                            <input type="hidden" name="action" value="vender_produto">
                                            <input type="hidden" name="id_produto" value="<?= $produto['id_produto'] ?>">
                                            <input type="number" name="quantidade" value="1" min="1" max="<?= $produto['quantidade'] ?>" class="form-control form-control-sm w-auto me-2" style="max-width: 80px;">
                                            <button type="submit" class="btn btn-sm btn-primary" <?= ($produto['quantidade'] == 0) ? 'disabled' : '' ?>>
                                                <i class="bi bi-cart-plus me-1"></i>Vender
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    Nenhum produto disponível para venda.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>