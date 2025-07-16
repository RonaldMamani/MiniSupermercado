<?php

require_once __DIR__ . '/../../lib/solicitacao.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id_solicitacao = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id_solicitacao) {
        if ($action === 'aprovar') {
            if (atualizarStatusSolicitacao($id_solicitacao, 'aprovado')) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Solicitação #' . $id_solicitacao . ' aprovada com sucesso!'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao aprovar solicitação #' . $id_solicitacao . '.'];
            }
        } elseif ($action === 'rejeitar') {
            if (atualizarStatusSolicitacao($id_solicitacao, 'negado')) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Solicitação #' . $id_solicitacao . ' rejeitada com sucesso!'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao rejeitar solicitação #' . $id_solicitacao . '.'];
            }
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'ID da solicitação inválido.'];
    }
    // Redireciona para evitar reenvio da requisição GET
    header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=financeiro');
    exit();
}

$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$solicitacoes = listarSolicitacoes();
?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-danger text-white">
            <h3 class="card-title mb-0"><i class="bi bi-wallet-fill me-2"></i>Painel Financeiro</h3>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h4 class="mb-3">Gerenciar Solicitações</h4>
            <?php if (!empty($solicitacoes)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="col">ID</th>
                                <th class="col">Solicitante</th>
                                <th class="col">Solicitação</th>
                                <th class="col">Status</th>
                                <th class="col">Data</th>
                                <th class="col" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitacoes as $solicitacao): ?>
                                <tr>
                                    <td><?= htmlspecialchars($solicitacao['id_solicitacao']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($solicitacao['tipo_perfil'])) ?></td>
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
                                    <td><?= htmlspecialchars($solicitacao['data_solicitacao']) ?></td>
                                    <td class="text-center">
                                        <?php if ($solicitacao['status'] == 'pendente'): ?>
                                            <a href="?panel=financeiro&action=aprovar&id=<?= $solicitacao['id_solicitacao'] ?>" class="btn btn-sm btn-success me-2">
                                                <i class="bi bi-check-circle me-1"></i>Aprovar
                                            </a>
                                            <a href="?panel=financeiro&action=rejeitar&id=<?= $solicitacao['id_solicitacao'] ?>" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle me-1"></i>Rejeitar
                                            </a>
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
                    <i class="bi bi-info-circle me-2"></i>Não há solicitações pendentes ou registradas.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>