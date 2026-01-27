<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-id-card me-2"></i> Perfil do Cliente</h3>
    <a href="<?= \BASE_URL ?>/clientes" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i> Voltar
    </a>
</div>

<div class="row">
    <!-- Client Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <span class="fa-stack fa-3x text-primary">
                        <i class="fa-solid fa-circle fa-stack-2x"></i>
                        <i class="fa-solid fa-user fa-stack-1x fa-inverse"></i>
                    </span>
                </div>
                <h5 class="card-title">
                    <?= htmlspecialchars($cliente['name']) ?>
                </h5>
                <p class="text-muted mb-1">
                    <?= htmlspecialchars($cliente['documento'] ?? 'Sem documento') ?>
                </p>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><i class="fa-solid fa-envelope me-2 text-muted"></i>
                        <?= htmlspecialchars($cliente['email'] ?? '-') ?>
                    </p>
                    <p class="mb-2"><i class="fa-solid fa-phone me-2 text-muted"></i>
                        <?= htmlspecialchars($cliente['phone'] ?? '-') ?>
                    </p>
                    <p class="mb-2"><i class="fa-solid fa-location-dot me-2 text-muted"></i>
                        <?= htmlspecialchars($cliente['address'] ?? '-') ?>
                    </p>
                    <p class="mb-0 small text-muted"><i class="fa-regular fa-calendar me-2"></i> Cadastrado em
                        <?= date('d/m/Y', strtotime($cliente['created_at'])) ?>
                    </p>
                </div>
                <div class="d-grid mt-3">
                    <a href="<?= \BASE_URL ?>/galeria/show/<?= $cliente['id'] ?>" class="btn btn-primary">
                        <i class="fa-solid fa-images me-2"></i> Ver Galeria
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- History Tabs -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="historyTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="os-tab" data-bs-toggle="tab" data-bs-target="#os"
                            type="button" role="tab"><i class="fa-solid fa-file-circle-check me-2"></i> Ordens de
                            Serviço</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="orc-tab" data-bs-toggle="tab" data-bs-target="#orc" type="button"
                            role="tab"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Orçamentos</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="ativos-tab" data-bs-toggle="tab" data-bs-target="#ativos"
                            type="button" role="tab"><i class="fa-solid fa-box me-2"></i> Ativos</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados"
                            type="button" role="tab"><i class="fa-solid fa-info-circle me-2"></i> Dados
                            Cadastrais</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="historyTabContent">

                    <!-- OS Tab -->
                    <div class="tab-pane fade show active" id="os" role="tabpanel">
                        <?php if (empty($historicoOS)): ?>
                            <p class="text-center text-muted my-4">Nenhuma ordem de serviço encontrada.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th># ID</th>
                                            <th>Data</th>
                                            <th>Status</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historicoOS as $os): ?>
                                            <tr>
                                                <td>
                                                    <?= $os['id'] ?>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                                </td>
                                                <td><span class="badge bg-secondary">
                                                        <?= $os['status'] ?>
                                                    </span></td>
                                                <td><a href="<?= \BASE_URL ?>/os/pdf/<?= $os['id'] ?>" target="_blank"
                                                        class="btn btn-xs btn-outline-primary"><i
                                                            class="fa-solid fa-file-pdf"></i></a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quotes Tab -->
                    <div class="tab-pane fade" id="orc" role="tabpanel">
                        <?php if (empty($historicoOrc)): ?>
                            <p class="text-center text-muted my-4">Nenhum orçamento encontrado.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th># ID</th>
                                            <th>Data</th>
                                            <th>Valor Total</th>
                                            <th>Status</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historicoOrc as $orc): ?>
                                            <tr>
                                                <td>
                                                    <?= $orc['id'] ?>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($orc['created_at'])) ?>
                                                </td>
                                                <td>R$
                                                    <?= number_format($orc['total'], 2, ',', '.') ?>
                                                </td>
                                                <td>
                                                    <?php if ($orc['status'] === 'Aprovado'): ?>
                                                        <span class="badge bg-success">Aprovado</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pendente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($orc['status'] === 'Pendente'): ?>
                                                        <a href="<?= \BASE_URL ?>/orcamentos/aprovar/<?= $orc['id'] ?>"
                                                            class="btn btn-xs btn-success" title="Aprovar"><i
                                                                class="fa-solid fa-check"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Assets Tab -->
                    <div class="tab-pane fade" id="ativos" role="tabpanel">
                        <?php if (empty($ativos)): ?>
                            <p class="text-center text-muted my-4">Nenhum ativo vinculado.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Descrição</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ativos as $ativo): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($ativo['name']) ?></td>
                                                <td><?= htmlspecialchars($ativo['description'] ?? '-') ?></td>
                                                <td>
                                                    <a href="<?= \BASE_URL ?>/ativos/detalhes/<?= $ativo['id'] ?>"
                                                        class="btn btn-xs btn-outline-primary" title="Ver Detalhes">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Dados Cadastrais Tab -->
                    <div class="tab-pane fade" id="dados" role="tabpanel">
                        <div class="row g-3">
                            <!-- Responsável -->
                            <div class="col-12">
                                <h6 class="border-bottom pb-2">Responsável</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Nome do Responsável</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['responsavel'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Cargo</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['cargo'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone 2</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['telefone2'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">CNPJ</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['cnpj'] ?? '-') ?></p>
                            </div>

                            <!-- Zeladoria -->
                            <div class="col-12 mt-3">
                                <h6 class="border-bottom pb-2">Zeladoria</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Nome do Zelador</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['zelador_nome'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Email do Zelador</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['zelador_email'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone Zelador</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['zelador_tel'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone 2 Zelador</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['zelador_tel2'] ?? '-') ?></p>
                            </div>

                            <!-- Síndico -->
                            <div class="col-12 mt-3">
                                <h6 class="border-bottom pb-2">Síndico</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Nome do Síndico</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['sindico_nome'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Email do Síndico</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['sindico_email'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone Síndico</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['sindico_tel'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone 2 Síndico</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['sindico_tel2'] ?? '-') ?></p>
                            </div>

                            <!-- Administradora -->
                            <div class="col-12 mt-3">
                                <h6 class="border-bottom pb-2">Administradora</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Nome Administradora</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['admin_nome'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Email Administradora</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['admin_email'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone Administradora</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['admin_tel'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Telefone 2 Administradora</label>
                                <p class="fw-bold"><?= htmlspecialchars($cliente['admin_tel2'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>