<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-file-invoice-dollar me-2"></i> Orçamentos</h3>
    <div>
        <a href="<?= \BASE_URL ?>/orcamentos/nova-versao" class="btn btn-warning me-2">
            <i class="fa-solid fa-file-word me-2"></i> Criar (Editor de Texto)
        </a>
        <a href="<?= \BASE_URL ?>/orcamentos/criar-v2" class="btn btn-outline-success me-2">
            <i class="fa-solid fa-list-check me-2"></i> Criar V2 (Hierárquico)
        </a>
        <a href="<?= \BASE_URL ?>/orcamentos/criar" class="btn btn-outline-primary">
            <i class="fa-solid fa-plus me-2"></i> Criar Padrão
        </a>
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= \BASE_URL ?>/orcamentos" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="client" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="client" name="client" placeholder="Nome do Cliente"
                    value="<?= htmlspecialchars($_GET['client'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="Pendente" <?= (($_GET['status'] ?? '') == 'Pendente') ? 'selected' : '' ?>>Pendente
                    </option>
                    <option value="Aprovado" <?= (($_GET['status'] ?? '') == 'Aprovado') ? 'selected' : '' ?>>Aprovado
                    </option>
                    <option value="Rejeitado" <?= (($_GET['status'] ?? '') == 'Rejeitado') ? 'selected' : '' ?>>Rejeitado
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="model" class="form-label">Modelo VRP</label>
                <select class="form-select" id="model" name="model">
                    <option value="">Todos</option>
                    <?php if (!empty($valve_models)): ?>
                        <?php foreach ($valve_models as $modelo): ?>
                            <option value="<?= htmlspecialchars($modelo['name']) ?>" <?= (($_GET['model'] ?? '') == $modelo['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($modelo['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_start" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="date_start" name="date_start"
                    value="<?= htmlspecialchars($_GET['date_start'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="date_end" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="date_end" name="date_end"
                    value="<?= htmlspecialchars($_GET['date_end'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-2"></i>
                    Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th scope="col"># ID</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Título / Modelo</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col">Data</th>
                        <th scope="col" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orcamentos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Nenhum orçamento encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orcamentos as $orc): ?>
                            <tr>
                                <td><strong>
                                        <?= $orc['id'] ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($orc['client_name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($orc['assunto'] ?? '-') ?>
                                </td>
                                <td class="fw-bold text-success">R$
                                    <?= number_format($orc['total'], 2, ',', '.') ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($orc['status'] == 'Pendente')
                                        $badgeClass = 'bg-warning text-dark';
                                    if ($orc['status'] == 'Aprovado')
                                        $badgeClass = 'bg-success';
                                    if ($orc['status'] == 'Rejeitado')
                                        $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $orc['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($orc['created_at'])) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= \BASE_URL ?>/orcamentos/pdf/<?= $orc['id'] ?>"
                                        class="btn btn-sm btn-outline-danger me-1" target="_blank" title="Gerar PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                    <a href="<?= \BASE_URL ?>/orcamentos/duplicar/<?= $orc['id'] ?>"
                                        class="btn btn-sm btn-outline-secondary" title="Utilizar como Base (Duplicar)">
                                        <i class="fa-solid fa-copy"></i>
                                    </a>
                                    <?php if ($orc['status'] == 'Pendente'): ?>
                                        <a href="<?= \BASE_URL ?>/orcamentos/aprovar/<?= $orc['id'] ?>"
                                            class="btn btn-sm btn-outline-success" title="Aprovar e Gerar OS"
                                            onclick="return confirm('Aprovar orçamento e gerar OS?');">
                                            <i class="fa-solid fa-check"></i>
                                        </a>
                                        <a href="<?= \BASE_URL ?>/orcamentos/edit/<?= $orc['id'] ?>"
                                            class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fa-solid fa-lock"></i> Processado
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>