<div class="card">
    <div class="card-header">
        Detalhes do Ativo
    </div>
    <div class="card-body">
        <h5 class="card-title">
            <?= htmlspecialchars($ativo['name']) ?>
        </h5>
        <p class="card-text"><strong>UUID:</strong>
            <?= htmlspecialchars($ativo['uuid']) ?>
        </p>
        <p class="card-text"><strong>Descrição:</strong>
            <?= htmlspecialchars($ativo['description']) ?>
        </p>
        <p class="card-text"><small class="text-muted">Criado em:
                <?= $ativo['created_at'] ?>
            </small></p>

        <hr>
        <h6>QR Code Permanente</h6>
        <div class="d-flex align-items-center">
            <div id="qrcode-view" class="me-3"></div>
            <div>
                <a href="<?= BASE_URL ?>/ativo/view/<?= $ativo['uuid'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir Link
                </a>
                <div class="text-muted small mt-1"><?= BASE_URL ?>/ativo/view/<?= $ativo['uuid'] ?></div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                new QRCode(document.getElementById("qrcode-view"), {
                    text: "<?= BASE_URL ?>/ativo/view/<?= $ativo['uuid'] ?>",
                    width: 128,
                    height: 128
                });
            });
        </script>
    </div>
    <div class="card-footer">
        <a href="<?= BASE_URL ?>/ativos" class="btn btn-secondary">Voltar</a>
    </div>
</div>