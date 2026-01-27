<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-list-check me-2"></i> Ordens de Serviço</h3>
    <a href="<?= \BASE_URL ?>/os/criar" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i> Nova OS
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= \BASE_URL ?>/os" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="client" class="form-label">Cliente</label>
                <input type="text" name="client" id="client" class="form-control" placeholder="Nome do cliente"
                    value="<?= $_GET['client'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="Aberto" <?= ($_GET['status'] ?? '') == 'Aberto' ? 'selected' : '' ?>>Aberto</option>
                    <option value="Em Andamento" <?= ($_GET['status'] ?? '') == 'Em Andamento' ? 'selected' : '' ?>>Em
                        Andamento</option>
                    <option value="Concluído" <?= ($_GET['status'] ?? '') == 'Concluído' ? 'selected' : '' ?>>Concluído
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_start" class="form-label">Data Início</label>
                <input type="date" name="date_start" id="date_start" class="form-control"
                    value="<?= $_GET['date_start'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label for="date_end" class="form-label">Data Fim</label>
                <input type="date" name="date_end" id="date_end" class="form-control"
                    value="<?= $_GET['date_end'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fa-solid fa-filter me-2"></i> Filtrar
                </button>
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
                        <th scope="col">Status</th>
                        <th scope="col">Data</th>
                        <th scope="col" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordens)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Nenhuma Ordem de Serviço encontrada.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ordens as $os): ?>
                            <tr>
                                <td><strong>
                                        <?= $os['id'] ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($os['client_name']) ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($os['status'] == 'Aberto')
                                        $badgeClass = 'bg-primary';
                                    if ($os['status'] == 'Em Andamento')
                                        $badgeClass = 'bg-warning text-dark';
                                    if ($os['status'] == 'Concluído')
                                        $badgeClass = 'bg-success';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $os['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($os['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= \BASE_URL ?>/os/edit/<?= $os['id'] ?>" class="btn btn-sm btn-outline-primary"
                                        title="Editar">
                                        <i class="fa-solid fa-edit"></i> Editar
                                    </a>
                                    <a href="<?= \BASE_URL ?>/os/pdf/<?= $os['id'] ?>" class="btn btn-sm btn-outline-danger"
                                        title="Gerar PDF" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>