<?php
require_once __DIR__ . '/../../lib/produto.php';
require_once __DIR__ . '/../../lib/solicitacao.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enviar_solicitacao_admin') {
    $nome_solicitacao = "Permissão para Editar Produtos";
    
    $id_perfil_solicitante = isset($_SESSION['id_perfil']) ? (int)$_SESSION['id_perfil'] : 0; 
    
    if ($id_perfil_solicitante === 0) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Não foi possível identificar seu perfil para enviar a solicitação. Por favor, faça login novamente.'];
        header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=admin');
        exit();
    }

    if (criarSolicitacao($id_perfil_solicitante, $nome_solicitacao)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Solicitação para editar produtos enviada com sucesso!'];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao enviar solicitação. Verifique os logs do servidor para mais detalhes.'];
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=admin');
    exit();
}

$produtos = listarProdutosPorId();
$solicitacoes = listarSolicitacoesPorId();
?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="bi bi-speedometer2 me-2"></i>Painel do Administrador</h3>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h4 class="mb-3">Visão Geral de Produtos</h4>
            <?php if (!empty($produtos)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="col">ID</th>
                                <th class="col">Produto</th>
                                <th class="col">Categoria</th>
                                <th class="col">Quantidade</th>
                                <th class="col">Preço</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($produto['id_produto'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($produto['nome_produto']) ?></td>
                                    <td><?= htmlspecialchars($produto['nome_categoria'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?= ($produto['quantidade'] > 15) ? 'success' : (($produto['quantidade'] > 5) ? 'warning text-dark' : 'danger') ?>">
                                            <?= htmlspecialchars($produto['quantidade']) ?>
                                        </span>
                                    </td>
                                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>Nenhum produto cadastrado.
                </div>
            <?php endif; ?>

            <h4 class="mb-3 mt-5">Gerenciar Solicitações</h4>
            <?php if (!empty($solicitacoes)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="col">ID</th>
                                <th class="col">Solicitante</th>
                                <th class="col">Nome da Solicitação</th>
                                <th class="col">Status</th>
                                <th class="col">Data</th>
                                <th class="col" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitacoes as $solicitacao): ?>
                                <tr>
                                    <td><?= htmlspecialchars($solicitacao['id_solicitacao']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($solicitacao['tipo_perfil'] ?? 'N/A')) ?></td>
                                    <td><?= htmlspecialchars($solicitacao['nome_solicitacao']) ?></td>
                                    <td>
                                        <?php
                                            $status_class = '';
                                            switch ($solicitacao['status']) {
                                                case 'pendente': $status_class = 'bg-warning text-dark'; break;
                                                case 'aprovado': $status_class = 'bg-success'; break;
                                                case 'negado': $status_class = 'bg-danger'; break;
                                                default: $status_class = 'bg-secondary'; break;
                                            }
                                        ?>
                                        <span class="badge rounded-pill <?= $status_class ?>"><?= htmlspecialchars(ucfirst($solicitacao['status'])) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($solicitacao['data_solicitacao'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <?php if ($solicitacao['status'] == 'pendente'): ?>
                                            <?php if (isset($_SESSION['tipo_perfil']) && ($_SESSION['tipo_perfil'] == 'financeiro' || $_SESSION['tipo_perfil'] == 'admin')): ?>
                                                <a href="?panel=financeiro&action=aprovar&id=<?= $solicitacao['id_solicitacao'] ?>" class="btn btn-sm btn-success me-2" title="Aprovar Solicitação">
                                                    <i class="bi bi-check-circle"></i> Aprovar
                                                </a>
                                                <a href="?panel=financeiro&action=rejeitar&id=<?= $solicitacao['id_solicitacao'] ?>" class="btn btn-sm btn-danger" title="Rejeitar Solicitação">
                                                    <i class="bi bi-x-circle"></i> Rejeitar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Aguardando Financeiro</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Ações concluídas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>Não há solicitações registradas.
                </div>
            <?php endif; ?>

            <hr class="my-5">

            <h4 class="mb-3">Solicitar Permissão de Edição de Estoque</h4>
            <div class="alert alert-secondary d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill flex-shrink-0 me-2"></i>
                <div>
                    Envie uma solicitação ao perfil "Financeiro" para obter permissão para adicionar, editar ou deletar produtos no painel de Estoque.
                </div>
            </div>
            <form method="post" action="" class="d-grid gap-2">
                <input type="hidden" name="action" value="enviar_solicitacao_admin">
                <button type="submit" class="btn btn-warning btn-lg text-dark">
                    <i class="bi bi-send-fill me-2"></i>Enviar Solicitação ao Financeiro
                </button>
            </form>
        </div>
    </div>
</div>