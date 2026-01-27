<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa-solid fa-file-signature me-2"></i> Nova Ordem de Serviço</h5>
    </div>
    <div class="card-body">
        <form action="<?= \BASE_URL ?>/os/store" method="POST" enctype="multipart/form-data">

            <!-- Cliente -->
            <div class="mb-3">
                <label for="client_id" class="form-label">Cliente</label>
                <select class="form-select searchable" id="client_id" name="client_id" required>
                    <option value="" selected disabled>Selecione um cliente...</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id'] ?>">
                            <?= htmlspecialchars($cliente['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Budget Link (Optional) -->
            <div class="mb-3">
                <label for="orcamento_id" class="form-label">Vincular Orçamento (Opcional)</label>
                <select class="form-select searchable" id="orcamento_id" name="orcamento_id">
                    <option value="">-- Sem Vínculo --</option>
                    <?php foreach ($orcamentos as $orc): ?>
                        <option value="<?= $orc['id'] ?>" data-client-id="<?= $orc['client_id'] ?>"
                            data-garantia="<?= htmlspecialchars($orc['garantia'] ?? '') ?>">
                            Orçamento #<?= $orc['id'] ?> - <?= htmlspecialchars($orc['client_name']) ?> (R$
                            <?= number_format($orc['total'], 2, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Apenas orçamentos pendentes são listados.</small>
            </div>

            <!-- Tipo de Serviço -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo de Serviço</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="Execução" selected>Execução</option>
                        <option value="Diagnóstico">Diagnóstico</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="validade_meses" class="form-label">Validade (Garantia)</label>
                    <select class="form-select" id="validade_meses" name="validade_meses">
                        <option value="0">Sem Validade / Pontual</option>
                        <option value="3">3 Meses</option>
                        <option value="6">6 Meses</option>
                        <option value="12">12 Meses (1 Ano)</option>
                        <option value="24">24 Meses (2 Anos)</option>
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Aberto">Aberto</option>
                    <option value="Em Andamento">Em Andamento</option>
                    <option value="Aguardando Peças">Aguardando Peças</option>
                    <option value="Concluído">Concluído</option>
                </select>
            </div>

            <!-- Relatório -->
            <div class="mb-3">
                <label for="relatorio" class="form-label">Relatório Técnico</label>
                <textarea class="form-control" id="relatorio" name="relatorio" rows="5"
                    placeholder="Descreva o serviço realizado..." required></textarea>
            </div>

            <!-- Aviso Importante -->
            <div class="mb-3">
                <label for="aviso" class="form-label">Aviso Importante (PDF)</label>
                <textarea class="form-control" id="aviso" name="aviso" rows="3"
                    placeholder="Ex: Garantia de 3 meses conforme orçamento..."></textarea>
                <small class="text-muted">Este texto aparecerá na seção "AVISO IMPORTANTE" do PDF.</small>
            </div>

            <!-- Fotos (Camera Input) -->
            <div class="mb-4">
                <label for="fotos" class="form-label d-block">Fotos do Serviço</label>
                <div class="d-grid">
                    <label class="btn btn-outline-primary btn-lg">
                        <i class="fa-solid fa-camera fa-lg me-2"></i> Tirar Fotos / Galeria
                        <input type="file" id="fotos" name="fotos[]" accept="image/*" capture="environment" multiple
                            hidden>
                    </label>
                </div>
                <div id="preview-area" class="mt-3 d-flex flex-wrap gap-2">
                    <!-- Previews will appear here -->
                </div>
                <small class="text-muted">Selecione múltiplas fotos se necessário.</small>
            </div>

            <!-- Submit -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fa-solid fa-check me-2"></i> Salvar Ordem de Serviço
                </button>
                <a href="<?= \BASE_URL ?>/ativos" class="btn btn-outline-secondary">Cancelar</a>
            </div>

        </form>
    </div>
</div>

<script>
    // Simple script to show preview of selected images
    document.getElementById('fotos').addEventListener('change', function (event) {
        const previewArea = document.getElementById('preview-area');
        previewArea.innerHTML = '';
        const files = event.target.files;

        if (files) {
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-thumbnail');
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    previewArea.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }
    });

    // Auto-fill Client and Validity when Budget is selected
    const selectOrcamento = document.getElementById('orcamento_id');
    const selectCliente = document.getElementById('client_id');
    const inputValidade = document.getElementById('validade_meses');

    selectOrcamento.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Auto Select Client
            const clientId = selectedOption.dataset.clientId;
            if (clientId) {
                selectCliente.value = clientId;
            }

            // Auto Fill Validity
            // Parse "06 (seis) meses" -> 6
            // Logic: find first number.
            const garantiaRaw = selectedOption.dataset.garantia;
            if (garantiaRaw) {
                const match = garantiaRaw.match(/\d+/);
                if (match) {
                    inputValidade.value = match[0];
                }
            }
        }
    });
</script>