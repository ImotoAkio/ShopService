<div class="container mt-4">
    <h2>Configurações do Sistema</h2>
    <hr>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            Numeração dos Documentos
        </div>
        <div class="card-body">
            <p class="text-muted">
                Dufina o número do próximo documento a ser gerado. útil para manter a continuidade de sistemas
                anteriores.
                <br>
                <strong>Nota:</strong> O número deve ser maior que o último documento criado.
            </p>

            <form action="<?= \BASE_URL ?>/configuracoes/update" method="POST">

                <div class="mb-3 row">
                    <label for="os_start_id" class="col-sm-4 col-form-label">Próximo Número de OS:</label>
                    <div class="col-sm-4">
                        <input type="number" class="form-control" id="os_start_id" name="os_start_id"
                            value="<?= $nextIdOS ?>" min="<?= $nextIdOS ?>">
                    </div>
                    <div class="col-sm-4">
                        <span class="form-text">Atual:
                            <?= $nextIdOS ?> (ou maior)
                        </span>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="orcamento_start_id" class="col-sm-4 col-form-label">Próximo Número de Orçamento:</label>
                    <div class="col-sm-4">
                        <input type="number" class="form-control" id="orcamento_start_id" name="orcamento_start_id"
                            value="<?= $nextIdOrc ?>" min="<?= $nextIdOrc ?>">
                    </div>
                    <div class="col-sm-4">
                        <span class="form-text">Atual:
                            <?= $nextIdOrc ?> (ou maior)
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>

            </form>
        </div>
    </div>
</div>