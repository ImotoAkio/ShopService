<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Orçamento #
            <?= $orcamento['id'] ?>
        </h5>
        <div>
            <a href="<?= \BASE_URL ?>/orcamentos/pdf/<?= $orcamento['id'] ?>" target="_blank"
                class="btn btn-sm btn-light text-primary">
                <i class="fa-solid fa-file-pdf me-1"></i> Gerar PDF
            </a>
            <?php if ($orcamento['status'] === 'Pendente'): ?>
                <a href="<?= \BASE_URL ?>/orcamentos/aprovar/<?= $orcamento['id'] ?>" class="btn btn-sm btn-success ms-2"
                    onclick="return confirm('Confirmar aprovação deste orçamento?')">
                    <i class="fa-solid fa-check me-1"></i> Aprovar
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">

        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small ls-1">Cliente</h6>
                <p class="mb-0 fw-bold fs-5">
                    <?= htmlspecialchars($orcamento['client_name']) ?>
                </p>
                <p class="mb-0"><small>
                        <?= htmlspecialchars($orcamento['client_email']) ?>
                    </small></p>
                <p class="mb-0"><small>
                        <?= htmlspecialchars($orcamento['client_phone']) ?>
                    </small></p>
            </div>
            <div class="col-md-6 text-end">
                <h6 class="text-muted text-uppercase small ls-1">Status</h6>
                <?php
                $statusClass = match ($orcamento['status']) {
                    'Pendente' => 'bg-warning text-dark',
                    'Aprovado' => 'bg-success',
                    'Rejeitado' => 'bg-danger',
                    default => 'bg-secondary'
                };
                ?>
                <span class="badge <?= $statusClass ?> fs-6">
                    <?= $orcamento['status'] ?>
                </span>
                <p class="mt-2 mb-0 text-muted">Data:
                    <?= date('d/m/Y H:i', strtotime($orcamento['created_at'])) ?>
                </p>
            </div>
        </div>

        <hr>

        <div class="mb-4">
            <h6 class="text-muted text-uppercase small ls-1">Assunto</h6>
            <p class="fw-bold">
                <?= htmlspecialchars($orcamento['assunto']) ?>
            </p>
        </div>

        <div class="mb-4">
            <h6 class="text-muted text-uppercase small ls-1">Descrição Geral</h6>
            <div class="p-3 bg-light rounded border">
                <?= nl2br(htmlspecialchars($orcamento['servico_descricao'])) ?>
            </div>
        </div>

        <?php if (!empty($itens)): ?>
            <div class="mb-4">
                <h6 class="text-muted text-uppercase small ls-1">Itens do Orçamento</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th class="text-center" style="width: 100px;">Qtd</th>
                                <th class="text-end" style="width: 140px;">Valor Unit.</th>
                                <th class="text-end" style="width: 140px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['description']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $item['quantity'] ?>
                                    </td>
                                    <td class="text-end">R$
                                        <?= number_format($item['unit_price'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end fw-bold">R$
                                        <?= number_format($item['total_price'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                                <td class="text-end fw-bold text-success fs-5">R$
                                    <?= number_format($orcamento['total'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small ls-1">Pagamento</h6>
                <p>
                    <?= htmlspecialchars($orcamento['forma_pagamento']) ?>
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small ls-1">Validade</h6>
                <p>
                    <?= htmlspecialchars($orcamento['validade']) ?>
                </p>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="<?= \BASE_URL ?>/orcamentos" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

    </div>
</div>