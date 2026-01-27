<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-server me-2"></i> Detalhes do Ativo (Interno)
        </h5>
        <div>
            <a href="<?= BASE_URL ?>/ativos/edit/<?= $ativo['id'] ?>" class="btn btn-warning btn-sm me-1">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
            <a href="<?= BASE_URL ?>/ativos" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Details Column -->
            <div class="col-md-5 border-end">
                <h3 class="text-primary fw-bold mb-3">
                    <?= htmlspecialchars($ativo['name']) ?>
                </h3>

                <p class="text-muted small mb-1 text-uppercase">Proprietário (Cliente)</p>
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid fa-user-tie text-secondary me-2"></i>
                    <span class="fw-bold fs-5">
                        <?= !empty($ativo['client_name']) ? htmlspecialchars($ativo['client_name']) : 'Não vinculado' ?>
                    </span>
                </div>

                <p class="text-muted small mb-1 text-uppercase">Descrição</p>
                <div class="bg-light p-3 rounded mb-3">
                    <?= nl2br(htmlspecialchars($ativo['description'])) ?>
                </div>

                <p class="text-muted small mb-1 text-uppercase">ID do Sistema</p>
                <code class="d-block mb-3"><?= htmlspecialchars($ativo['uuid']) ?></code>

                <hr>

                <!-- QR Code Section -->
                <div class="bg-white border rounded p-3 text-center">
                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-qrcode me-2"></i> QR Code Público</h6>
                    <div class="d-flex justify-content-center mb-3">
                        <div id="qrcode-view"></div>
                    </div>
                    <p class="small text-muted mb-2">
                        Escaneie este código para acessar a página pública do equipamento.
                    </p>
                    <a href="<?= BASE_URL ?>/ativo/view/<?= $ativo['uuid'] ?>" target="_blank"
                        class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir Link Público
                    </a>

                    <!-- Script for QR Code -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            new QRCode(document.getElementById("qrcode-view"), {
                                text: "<?= BASE_URL ?>/ativo/view/<?= $ativo['uuid'] ?>",
                                width: 150,
                                height: 150
                            });
                        });
                    </script>
                </div>
            </div>

            <!-- History Column -->
            <div class="col-md-7 ps-md-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2"></i> Histórico de Serviços</h5>

                <?php if (empty($historico)): ?>
                    <div class="alert alert-info">
                        <i class="fa-solid fa-info-circle me-2"></i> Nenhum serviço realizado neste equipamento.
                    </div>
                    <div class="text-center mt-4">
                        <a href="<?= BASE_URL ?>/os/criar" class="btn btn-success">
                            <i class="fa-solid fa-plus me-2"></i> Nova Ordem de Serviço
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>OS #</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Defeito</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $os): ?>
                                    <tr>
                                        <td><strong>
                                                <?= $os['id'] ?>
                                            </strong></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                        </td>
                                        <td><span class="badge bg-secondary">
                                                <?= htmlspecialchars($os['status']) ?>
                                            </span></td>
                                        <td>
                                            <?= mb_strimwidth(htmlspecialchars($os['relatorio']), 0, 30, "...") ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/os/pdf/<?= $os['id'] ?>" target="_blank"
                                                class="btn btn-xs btn-danger" title="PDF">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>