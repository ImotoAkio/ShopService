<h3 class="mb-4"><i class="fa-solid fa-gauge-high me-2"></i> Visão Geral</h3>

<!-- Summary Cards -->
<div class="row mb-4">
    <!-- OS Card -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">OS em Aberto</div>
                        <div class="h3 mb-0 text-primary">
                            <?= $stats['open_os'] ?>
                        </div>
                    </div>
                    <div class="text-primary opacity-25">
                        <i class="fa-solid fa-screwdriver-wrench fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotes Card -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Orçamentos Pendentes</div>
                        <div class="h3 mb-0 text-warning">
                            <?= $stats['pending_quotes'] ?>
                        </div>
                    </div>
                    <div class="text-warning opacity-25">
                        <i class="fa-solid fa-file-invoice-dollar fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients Card -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Clientes Total</div>
                        <div class="h3 mb-0 text-success">
                            <?= $stats['total_clientes'] ?>
                        </div>
                    </div>
                    <div class="text-success opacity-25">
                        <i class="fa-solid fa-users fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assets Card -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small fw-bold mb-1">Ativos Registrados</div>
                        <div class="h3 mb-0 text-info">
                            <?= $stats['total_assets'] ?>
                        </div>
                    </div>
                    <div class="text-info opacity-25">
                        <i class="fa-solid fa-boxes-stacked fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
<?php if (!empty($maintenance_alerts)): ?>
    <div class="alert alert-danger shadow-sm mb-4" role="alert">
        <h5 class="alert-heading"><i class="fa-solid fa-triangle-exclamation me-2"></i> Manutenções Vencidas ou Próximas
        </h5>
        <p>Os seguintes serviços requerem atenção imediata ou nova manutenção:</p>
        <div class="table-responsive bg-white rounded">
            <table class="table mb-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th>OS #</th>
                        <th>Cliente</th>
                        <th>Data Manutenção</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenance_alerts as $alert): ?>
                        <tr>
                            <td><strong>#<?= $alert['id'] ?></strong></td>
                            <td><?= htmlspecialchars($alert['client_name']) ?></td>
                            <td class="text-danger fw-bold">
                                <?= date('d/m/Y', strtotime($alert['data_proxima_manutencao'])) ?>
                            </td>
                            <td>
                                <a href="<?= \BASE_URL ?>/os/edit/<?= $alert['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Ver OS
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Activity & Quick Actions -->
<div class="row">
    <!-- Recent OS Table -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Ordens de Serviço
                    Recentes</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_os)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Nenhuma atividade recente.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_os as $os): ?>
                                <tr>
                                    <td><strong>#
                                            <?= $os['id'] ?>
                                        </strong></td>
                                    <td>
                                        <?= htmlspecialchars($os['client_name']) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        if ($os['status'] === 'Aberto')
                                            $statusClass = 'bg-primary';
                                        if ($os['status'] === 'Em Andamento')
                                            $statusClass = 'bg-warning text-dark';
                                        if ($os['status'] === 'Concluído')
                                            $statusClass = 'bg-success';
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= $os['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= \BASE_URL ?>/os/pdf/<?= $os['id'] ?>" target="_blank"
                                            class="btn btn-sm btn-outline-secondary" title="Ver PDF">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="<?= \BASE_URL ?>/os" class="text-decoration-none">Ver todas as OS <i
                        class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary"><i class="fa-solid fa-bolt me-2"></i>Ações Rápidas</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= \BASE_URL ?>/os" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <i class="fa-solid fa-plus-circle text-primary me-2"></i> Nova Ordem de Serviço
                        </div>
                        <small class="text-muted"><i class="fa-solid fa-chevron-right"></i></small>
                    </div>
                </a>
                <a href="<?= \BASE_URL ?>/clientes/criar" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <i class="fa-solid fa-user-plus text-success me-2"></i> Cadastrar Cliente
                        </div>
                        <small class="text-muted"><i class="fa-solid fa-chevron-right"></i></small>
                    </div>
                </a>
                <a href="<?= \BASE_URL ?>/orcamentos" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <i class="fa-solid fa-file-invoice text-warning me-2"></i> Novo Orçamento
                        </div>
                        <small class="text-muted"><i class="fa-solid fa-chevron-right"></i></small>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>