<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-chart-line me-2"></i> Dashboard Financeiro</h3>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalTags">
            <i class="fa-solid fa-tags me-2"></i> Gerenciar Tags
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLancamento">
            <i class="fa-solid fa-plus me-2"></i> Novo Lançamento
        </button>
    </div>
</div>

<!-- Dashboard Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small">Receitas (Filtro)</h6>
                <h3 class="text-success fw-bold">R$
                    <?= number_format($totalReceita, 2, ',', '.') ?>
                </h3>
                <small class="text-muted">Recebido: R$ <?= number_format($totalRecebido, 2, ',', '.') ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small">Despesas (Filtro)</h6>
                <h3 class="text-danger fw-bold">R$
                    <?= number_format($totalDespesa, 2, ',', '.') ?>
                </h3>
                <small class="text-muted">Pendente: R$ <?= number_format($totalPagar, 2, ',', '.') ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small">Saldo Resultante (Filtro)</h6>
                <h3 class="<?= $saldoResultante >= 0 ? 'text-primary' : 'text-danger' ?> fw-bold">
                    R$
                    <?= number_format($saldoResultante, 2, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gerenciar Tags -->
<div class="modal fade" id="modalTags" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gerenciar Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= \BASE_URL ?>/financeiro/tags/store" method="POST" class="input-group mb-3">
                    <input type="text" name="tag_name" class="form-control" placeholder="Nova Tag..." required>
                    <input type="color" name="tag_color" class="form-control form-control-color" value="#6c757d"
                        title="Cor da Tag">
                    <button class="btn btn-outline-success" type="submit"><i class="fa-solid fa-plus"></i></button>
                </form>

                <div class="list-group">
                    <?php foreach ($allTags as $tag): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa-solid fa-circle me-2"
                                    style="color: <?= htmlspecialchars($tag['color'] ?? '#6c757d') ?>"></i>
                                <?= htmlspecialchars($tag['name']) ?>
                            </span>
                            <form action="<?= \BASE_URL ?>/financeiro/tags/delete" method="POST" class="d-inline"
                                onsubmit="return confirm('Tem certeza que deseja excluir esta tag?');">
                                <input type="hidden" name="tag_id" value="<?= $tag['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i
                                        class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= \BASE_URL ?>/financeiro" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tags</label>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary w-100 dropdown-toggle text-start text-truncate"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                        if (empty($filterTags)) {
                            echo 'Selecione...';
                        } else {
                            echo count($filterTags) . ' selecionada(s)';
                        }
                        ?>
                    </button>
                    <ul class="dropdown-menu p-3 w-100 shadow" style="max-height: 250px; overflow-y: auto;">
                        <?php foreach ($allTags as $tag): ?>
                            <li class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                    id="filter_tag_<?= $tag['id'] ?>" <?= in_array($tag['id'], $filterTags) ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="filter_tag_<?= $tag['id'] ?>">
                                    <i class="fa-solid fa-circle me-1 small"
                                        style="color: <?= htmlspecialchars($tag['color'] ?? '#6c757d') ?>"></i>
                                    <?= htmlspecialchars($tag['name']) ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">OS ID</label>
                <input type="number" name="os_id" class="form-control" placeholder="ID"
                    value="<?= htmlspecialchars($filterOs) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="receita" <?= $filterType == 'receita' ? 'selected' : '' ?>>Receita</option>
                    <option value="despesa" <?= $filterType == 'despesa' ? 'selected' : '' ?>>Despesa</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Período</label>
                <div class="input-group">
                    <input type="date" name="data_inicio" class="form-control"
                        value="<?= htmlspecialchars($filterStart) ?>">
                    <span class="input-group-text">-</span>
                    <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($filterEnd) ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary w-100 me-2"><i
                        class="fa-solid fa-filter"></i></button>
                <a href="<?= \BASE_URL ?>/financeiro" class="btn btn-outline-secondary" title="Limpar"><i
                        class="fa-solid fa-xmark"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Listagem -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">Últimos Lançamentos</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Desc</th>
                    <th>Tags</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th>OS #</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lancamentos as $lan): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($lan['descricao']) ?>
                        </td>
                        <td>
                            <?php if (!empty($lan['tags'])): ?>
                                <?php foreach (explode(',', $lan['tags']) as $tagData): ?>
                                    <?php
                                    $parts = explode('|', $tagData);
                                    $tagName = $parts[0] ?? '';
                                    $tagColor = $parts[1] ?? '#6c757d';
                                    if (!$tagName)
                                        continue;
                                    ?>
                                    <span class="badge mb-1"
                                        style="background-color: <?= htmlspecialchars($tagColor) ?>"><?= htmlspecialchars(trim($tagName)) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($lan['tipo'] == 'receita'): ?>
                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-arrow-up"></i>
                                    Receita</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-arrow-down"></i>
                                    Despesa</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold">R$
                            <?= number_format($lan['valor'], 2, ',', '.') ?>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($lan['data_vencimento'])) ?>
                        </td>
                        <td>
                            <form action="<?= \BASE_URL ?>/financeiro/status/toggle" method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $lan['id'] ?>">
                                <?php if ($lan['status'] == 'pago'): ?>
                                    <button type="submit" class="btn badge bg-success border-0" title="Marcar como Pendente">
                                        Pago <i class="fa-solid fa-check ms-1"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn badge bg-warning text-dark border-0"
                                        title="Marcar como Pago">
                                        Pendente <i class="fa-regular fa-clock ms-1"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td>
                            <?php if ($lan['os_id_vinculo']): ?>
                                <a href="<?= \BASE_URL ?>/os/pdf/<?= $lan['os_id_vinculo'] ?>" target="_blank">#
                                    <?= $lan['os_id_vinculo'] ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Novo Lançamento -->
<div class="modal fade" id="modalLancamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= \BASE_URL ?>/financeiro/store" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Descrição</label>
                        <input type="text" name="descricao" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tags</label>
                        <div class="d-flex flex-wrap gap-2" style="max-height: 150px; overflow-y: auto;">
                            <?php if (empty($allTags)): ?>
                                <small class="text-muted">Nenhuma tag cadastrada.</small>
                            <?php else: ?>
                                <?php foreach ($allTags as $tag): ?>
                                    <input type="checkbox" class="btn-check" id="tag_<?= $tag['id'] ?>" name="tags[]"
                                        value="<?= $tag['id'] ?>" autocomplete="off">
                                    <label class="btn btn-outline-secondary btn-sm rounded-pill"
                                        style="--bs-btn-active-bg: <?= htmlspecialchars($tag['color'] ?? '#6c757d') ?>; --bs-btn-active-border-color: <?= htmlspecialchars($tag['color'] ?? '#6c757d') ?>;"
                                        for="tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Valor</label>
                            <input type="number" name="valor" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Vencimento</label>
                            <input type="date" name="data_vencimento" value="<?= date('Y-m-d') ?>" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="pago" selected>Pago</option>
                                <option value="pendente">Pendente</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Vincular a Ordem de Serviço (Opcional)</label>
                        <select name="os_id" class="form-select">
                            <option value="">Sem vínculo</option>
                            <?php foreach ($osList as $osItem): ?>
                                <option value="<?= $osItem['id'] ?>">OS #
                                    <?= $osItem['id'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>