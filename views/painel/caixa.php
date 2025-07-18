<?php

require_once __DIR__ . '/../../lib/produto.php';
require_once __DIR__ . '/../../lib/cliente.php';

$id_usuario_logado = $_SESSION['user_id'] ?? 1;

if (!isset($_SESSION['carrinho_venda'])) {
    $_SESSION['carrinho_venda'] = [];
}

if (!isset($_SESSION['id_cliente_venda'])) {
    $_SESSION['id_cliente_venda'] = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ação: Selecionar Cliente
    if ($action === 'selecionar_cliente') {
        $id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
        if ($id_cliente) {
            $_SESSION['id_cliente_venda'] = $id_cliente;
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Cliente selecionado com sucesso!'];
        } else {
            $_SESSION['id_cliente_venda'] = null;
            $_SESSION['message'] = ['type' => 'info', 'text' => 'Selecione um cliente para iniciar a venda.'];
        }
    }

    // Ação: Adicionar Produto ao Carrinho
    if ($action === 'adicionar_ao_carrinho') {
        $id_produto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $quantidade_desejada = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
        $preco_unitario_produto = filter_input(INPUT_POST, 'preco_unitario', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $nome_produto = filter_input(INPUT_POST, 'nome_produto');
        $estoque_disponivel = filter_input(INPUT_POST, 'estoque_disponivel', FILTER_VALIDATE_INT);


        if ($id_produto && $quantidade_desejada > 0 && $preco_unitario_produto !== false && $nome_produto && $estoque_disponivel !== false) {
            // Verifica se a quantidade desejada é maior que o estoque total disponível para o produto
            if ($quantidade_desejada > $estoque_disponivel) {
                 $_SESSION['message'] = ['type' => 'danger', 'text' => "Quantidade desejada ({$quantidade_desejada}) para {$nome_produto} excede o estoque disponível ({$estoque_disponivel})."];
            } else {
                // Verifica se o produto já está no carrinho
                if (isset($_SESSION['carrinho_venda'][$id_produto])) {
                    $quantidade_atual_no_carrinho = $_SESSION['carrinho_venda'][$id_produto]['quantidade'];
                    $nova_quantidade_total = $quantidade_atual_no_carrinho + $quantidade_desejada;

                    if ($nova_quantidade_total > $estoque_disponivel) {
                        $_SESSION['message'] = ['type' => 'danger', 'text' => "Ao adicionar, a quantidade total de {$nome_produto} ({$nova_quantidade_total}) excede o estoque disponível ({$estoque_disponivel})."];
                    } else {
                        $_SESSION['carrinho_venda'][$id_produto]['quantidade'] = $nova_quantidade_total;
                        $_SESSION['message'] = ['type' => 'success', 'text' => "Quantidade de {$nome_produto} atualizada no carrinho para {$nova_quantidade_total}."];
                    }
                } else {
                    // Adiciona o novo item ao carrinho
                    $_SESSION['carrinho_venda'][$id_produto] = [
                        'id_produto' => $id_produto,
                        'nome_produto' => $nome_produto,
                        'quantidade' => $quantidade_desejada,
                        'preco_unitario' => $preco_unitario_produto,
                        'estoque_disponivel' => $estoque_disponivel
                    ];
                    $_SESSION['message'] = ['type' => 'success', 'text' => "Produto '{$nome_produto}' adicionado ao carrinho."];
                }
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Dados do produto inválidos para adicionar ao carrinho.'];
        }
    }

    // Ação: Remover Item do Carrinho
    if ($action === 'remover_do_carrinho') {
        $id_produto_remover = filter_input(INPUT_POST, 'id_produto_remover', FILTER_VALIDATE_INT);
        if ($id_produto_remover && isset($_SESSION['carrinho_venda'][$id_produto_remover])) {
            $nome_produto_removido = $_SESSION['carrinho_venda'][$id_produto_remover]['nome_produto'];
            unset($_SESSION['carrinho_venda'][$id_produto_remover]);
            $_SESSION['message'] = ['type' => 'info', 'text' => "Produto '{$nome_produto_removido}' removido do carrinho."];
        } else {
            $_SESSION['message'] = ['type' => 'warning', 'text' => 'Não foi possível remover o item do carrinho.'];
        }
    }

    // Ação: Atualizar Quantidade no Carrinho
    if ($action === 'atualizar_quantidade_carrinho') {
        $id_produto_atualizar = filter_input(INPUT_POST, 'id_produto_atualizar', FILTER_VALIDATE_INT);
        $nova_quantidade = filter_input(INPUT_POST, 'nova_quantidade', FILTER_VALIDATE_INT);

        if ($id_produto_atualizar && isset($_SESSION['carrinho_venda'][$id_produto_atualizar]) && $nova_quantidade > 0) {
            $item_carrinho = &$_SESSION['carrinho_venda'][$id_produto_atualizar];
            $nome_produto_atualizar = $item_carrinho['nome_produto'];

            if ($nova_quantidade > $item_carrinho['estoque_disponivel']) {
                $_SESSION['message'] = ['type' => 'danger', 'text' => "Quantidade de {$nome_produto_atualizar} ({$nova_quantidade}) excede o estoque disponível ({$item_carrinho['estoque_disponivel']})."];
            } else {
                $item_carrinho['quantidade'] = $nova_quantidade;
                $_SESSION['message'] = ['type' => 'success', 'text' => "Quantidade de {$nome_produto_atualizar} atualizada para {$nova_quantidade}."];
            }
        } else {
            $_SESSION['message'] = ['type' => 'warning', 'text' => 'Dados inválidos para atualizar quantidade.'];
        }
    }

    // Ação: Limpar Carrinho
    if ($action === 'limpar_carrinho') {
        $_SESSION['carrinho_venda'] = [];
        $_SESSION['message'] = ['type' => 'info', 'text' => 'Carrinho limpo.'];
    }

    // Ação: Finalizar Venda
    if ($action === 'finalizar_venda') {
        $id_cliente = $_SESSION['id_cliente_venda'];
        $produtos_para_venda = array_values($_SESSION['carrinho_venda']);

        $itens_para_registrar = [];
        foreach ($produtos_para_venda as $item) {
            $itens_para_registrar[] = [
                'id_produto' => $item['id_produto'],
                'quantidade_vendida' => $item['quantidade'],
                'preco_unitario_no_momento_da_venda' => $item['preco_unitario']
            ];
        }

        if ($id_cliente && !empty($itens_para_registrar)) {
            $venda_resultado = registrarVenda($id_usuario_logado, $id_cliente, $itens_para_registrar);

            if ($venda_resultado) {
                $cliente_info = obterClientePorId($id_cliente);
                $nome_cliente_venda = $cliente_info['nome'] ?? 'Cliente Desconhecido';

                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Venda para ' . htmlspecialchars($nome_cliente_venda) . ' (Total: R$ ' . number_format($venda_resultado['total_venda'], 2, ',', '.') . ') realizada com sucesso!'
                ];
                $_SESSION['carrinho_venda'] = [];
                $_SESSION['id_cliente_venda'] = null;
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro ao finalizar a venda. Verifique o estoque ou dados.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Selecione um cliente e adicione produtos ao carrinho para finalizar a venda.'];
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?painel=caixa');
    exit();
}

$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$produtos_disponiveis = listarProdutos();
$clientes_disponiveis = listarClientes();

// Calcula o total do carrinho para exibição
$total_carrinho = 0;
foreach ($_SESSION['carrinho_venda'] as $item) {
    $total_carrinho += ($item['quantidade'] * $item['preco_unitario']);
}

// Busca o nome do cliente selecionado para exibição
$nome_cliente_selecionado = 'Nenhum cliente selecionado';
if ($_SESSION['id_cliente_venda']) {
    foreach ($clientes_disponiveis as $cliente) {
        if ($cliente['id_cliente'] == $_SESSION['id_cliente_venda']) {
            $nome_cliente_selecionado = htmlspecialchars($cliente['nome']);
            break;
        }
    }
}

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

            <h4 class="mb-3">Selecione o Cliente</h4>
            <div class="mb-4">
                <form method="post" action="">
                    <input type="hidden" name="action" value="selecionar_cliente">
                    <div class="input-group">
                        <select id="selectCliente" name="id_cliente" class="form-select">
                            <option value="">Selecione um Cliente</option>
                            <?php foreach ($clientes_disponiveis as $cliente): ?>
                                <option value="<?= htmlspecialchars($cliente['id_cliente']) ?>"
                                    <?= ($_SESSION['id_cliente_venda'] == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-info"><i class="bi bi-person-check me-1"></i>Confirmar Cliente</button>
                    </div>
                    <p class="mt-2">Cliente Selecionado: <strong><?= $nome_cliente_selecionado ?></strong></p>
                </form>
            </div>

            <h4 class="mb-3">Lista de Produtos</h4>
            <?php if (!empty($produtos_disponiveis)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="col">Produto</th>
                                <th class="col">Preço</th>
                                <th class="col">Estoque</th>
                                <th class="col text-center">Adicionar ao Carrinho</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos_disponiveis as $produto): ?>
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
                                            <input type="hidden" name="action" value="adicionar_ao_carrinho">
                                            <input type="hidden" name="id_produto" value="<?= $produto['id_produto'] ?>">
                                            <input type="hidden" name="preco_unitario" value="<?= $produto['preco'] ?>">
                                            <input type="hidden" name="nome_produto" value="<?= htmlspecialchars($produto['nome_produto']) ?>">
                                            <input type="hidden" name="estoque_disponivel" value="<?= $produto['quantidade'] ?>">
                                            <input type="number" name="quantidade" value="1" min="1" max="<?= $produto['quantidade'] ?>" 
                                                class="form-control form-control-sm w-auto me-2" style="max-width: 80px;"
                                                <?= ($produto['quantidade'] == 0) ? 'disabled' : '' ?>>
                                            <button type="submit" class="btn btn-sm btn-primary" 
                                                <?= ($produto['quantidade'] == 0) ? 'disabled' : '' ?>>
                                                <i class="bi bi-cart-plus me-1"></i>Adicionar
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

            <h4 class="mb-3">Carrinho de Compras</h4>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Produto</th>
                            <th>Preço Unit.</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($_SESSION['carrinho_venda'])): ?>
                            <tr><td colspan="5" class="text-center">Nenhum item no carrinho.</td></tr>
                        <?php else: ?>
                            <?php foreach ($_SESSION['carrinho_venda'] as $id_produto_carrinho => $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                                    <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                                    <td>
                                        <form method="post" action="" class="d-flex align-items-center">
                                            <input type="hidden" name="action" value="atualizar_quantidade_carrinho">
                                            <input type="hidden" name="id_produto_atualizar" value="<?= $item['id_produto'] ?>">
                                            <input type="number" name="nova_quantidade" value="<?= $item['quantidade'] ?>" 
                                                min="1" max="<?= $item['estoque_disponivel'] ?>" 
                                                class="form-control form-control-sm w-auto me-2" style="max-width: 80px;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Atualizar Quantidade">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>R$ <?= number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.') ?></td>
                                    <td>
                                        <form method="post" action="" class="d-inline-block">
                                            <input type="hidden" name="action" value="remover_do_carrinho">
                                            <input type="hidden" name="id_produto_remover" value="<?= $item['id_produto'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Remover Item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total da Venda:</th>
                            <th id="totalCarrinho" colspan="2">R$ <?= number_format($total_carrinho, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between mb-4">
                <form method="post" action="">
                    <input type="hidden" name="action" value="limpar_carrinho">
                    <button type="submit" class="btn btn-secondary" 
                        <?= empty($_SESSION['carrinho_venda']) ? 'disabled' : '' ?>>
                        <i class="bi bi-x-circle me-2"></i>Limpar Carrinho
                    </button>
                </form>
                <form method="post" action="">
                    <input type="hidden" name="action" value="finalizar_venda">
                    <button type="submit" class="btn btn-success btn-lg" 
                        <?= (empty($_SESSION['carrinho_venda']) || !$_SESSION['id_cliente_venda']) ? 'disabled' : '' ?>>
                        <i class="bi bi-cart-check me-2"></i>Finalizar Venda
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>