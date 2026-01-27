<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-pen-to-square me-2"></i> Editar Modelo VRP</h2>
    <a href="<?= \BASE_URL ?>/valves" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i> Voltar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= \BASE_URL ?>/valves/update" method="POST">
                    <input type="hidden" name="id" value="<?= $valve['id'] ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nome do Modelo</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name"
                            value="<?= htmlspecialchars($valve['name']) ?>" required>
                    </div>

                    <div class="alert alert-info">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Tags disponíveis: <code>[QTD]</code>, <code>[MODELO]</code>, <code>[LOCAL]</code>,
                        <code>[TORRE]</code>, <code>[PDS]</code>, <code>[ALCANCE]</code>.
                    </div>

                    <div class="mb-3">
                        <label for="service_description" class="form-label fw-bold">Descrição do Serviço
                            (Padrão)</label>
                        <textarea class="form-control" id="service_description" name="service_description"
                            rows="3"><?= htmlspecialchars($valve['service_description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="procedures" class="form-label fw-bold">Procedimentos (Padrão)</label>
                        <textarea class="form-control" id="procedures" name="procedures"
                            rows="8"><?= htmlspecialchars($valve['procedures']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="observations" class="form-label fw-bold">Observações (Padrão)</label>
                        <textarea class="form-control" id="observations" name="observations"
                            rows="4"><?= htmlspecialchars($valve['observations']) ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-save me-2"></i> Atualizar Modelo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function () {
        $('#service_description, #procedures, #observations').summernote({
            height: 200,
            lang: 'pt-BR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    });
</script>