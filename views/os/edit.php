<h2><i class="fa-solid fa-edit me-2"></i> Editar Ordem de Serviço #
    <?= $os['id'] ?>
</h2>

<form action="<?= \BASE_URL ?>/os/update" method="POST" enctype="multipart/form-data" class="mt-4">
    <input type="hidden" name="id" value="<?= $os['id'] ?>">

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Informações Principais</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="client_id" class="form-label">Cliente</label>
                    <select name="client_id" id="client_id" class="form-select searchable" required>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= $os['client_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                <?= $cliente['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="orcamento_id" class="form-label">Vincular Orçamento (Opcional)</label>
                    <select name="orcamento_id" id="orcamento_id" class="form-select searchable">
                        <option value="">-- Sem Vínculo --</option>
                        <?php foreach ($orcamentos as $orc): ?>
                            <option value="<?= $orc['id'] ?>" <?= ($os['orcamento_id'] ?? '') == $orc['id'] ? 'selected' : '' ?>>
                                Orçamento #<?= $orc['id'] ?> - <?= date('d/m/Y', strtotime($orc['created_at'])) ?> (R$ <?= number_format($orc['total'], 2, ',', '.') ?>) [<?= $orc['status'] ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="tipo" class="form-label">Tipo de Serviço</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="Execução" <?= ($os['tipo'] ?? 'Execução') == 'Execução' ? 'selected' : '' ?>>Execução</option>
                        <option value="Diagnóstico" <?= ($os['tipo'] ?? '') == 'Diagnóstico' ? 'selected' : '' ?>>Diagnóstico</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="validade_meses" class="form-label">Validade (Garantia)</label>
                    <select class="form-select" id="validade_meses" name="validade_meses">
                        <option value="0" <?= ($os['validade_meses'] == 0) ? 'selected' : '' ?>>Sem Validade</option>
                        <option value="3" <?= ($os['validade_meses'] == 3) ? 'selected' : '' ?>>3 Meses</option>
                        <option value="6" <?= ($os['validade_meses'] == 6) ? 'selected' : '' ?>>6 Meses</option>
                        <option value="12" <?= ($os['validade_meses'] == 12) ? 'selected' : '' ?>>12 Meses (1 Ano)</option>
                        <option value="24" <?= ($os['validade_meses'] == 24) ? 'selected' : '' ?>>24 Meses (2 Anos)</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Próxima Manutenção</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                        <input type="text" class="form-control" name="data_proxima_manutencao" id="data_proxima_manutencao"
                               placeholder="dd/mm/aaaa"
                               value="<?= !empty($os['data_proxima_manutencao']) ? date('d/m/Y', strtotime($os['data_proxima_manutencao'])) : '' ?>">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status Atual</label>

                    <!-- Hidden input to store status, updated by buttons -->
                    <input type="hidden" name="status" id="status_input" value="<?= $os['status'] ?>">

                    <div class="d-flex align-items-center gap-2">
                        <div class="badge bg-secondary p-2 fs-6" id="status_display">
                            <?= $os['status'] ?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label d-block text-muted small">Progresso do Serviço:</label>
                        <div class="btn-group" role="group">
                            <button type="button"
                                class="btn btn-outline-primary <?= $os['status'] == 'Aberto' ? 'active' : '' ?>"
                                onclick="setStatus('Aberto')">
                                <i class="fa-solid fa-folder-open"></i> Aberto
                            </button>
                            <button type="button"
                                class="btn btn-outline-warning <?= $os['status'] == 'Em Andamento' ? 'active' : '' ?>"
                                onclick="setStatus('Em Andamento')">
                                <i class="fa-solid fa-hammer"></i> Em Andamento
                            </button>
                            <button type="button"
                                class="btn btn-outline-success <?= $os['status'] == 'Concluído' ? 'active' : '' ?>"
                                onclick="setStatus('Concluído')">
                                <i class="fa-solid fa-check-circle"></i> Concluído
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="relatorio" class="form-label">Relatório Técnico</label>
                <textarea name="relatorio" id="relatorio" class="form-control" rows="5"
                    required><?= htmlspecialchars($os['relatorio']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="aviso" class="form-label">Aviso Importante (PDF)</label>
                <textarea name="aviso" id="aviso" class="form-control" rows="3"
                    placeholder="Ex: Garantia de 3 meses..."><?= htmlspecialchars($os['aviso'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="fotos" class="form-label">Adicionar Mais Fotos</label>
                <input type="file" name="fotos[]" id="fotos" class="form-control" multiple accept="image/*">
            </div>

            <?php if (!empty($photos)): ?>
                <div class="mb-3">
                    <label class="form-label">Fotos Existentes</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($photos as $photo): ?>
                            <div class="border p-1 rounded">
                                <img src="<?= \ASSET_URL . $photo['photo_path'] ?>" alt="Foto OS"
                                    style="height: 100px; width: auto; object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <div class="card-footer text-end">
            <a href="<?= \BASE_URL ?>/os" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save me-2"></i> Salvar Alterações
            </button>
        </div>
    </div>
</form>

<script>
    function setStatus(status) {
        document.getElementById('status_input').value = status;
        document.getElementById('status_display').innerText = status;

        // Visual Update
        const btns = document.querySelectorAll('.btn-group .btn');
        btns.forEach(btn => {
            btn.classList.remove('active');
            if (btn.innerText.includes(status)) {
                btn.classList.add('active');
            }
        });

        // Update badge color
        const badge = document.getElementById('status_display');
        badge.className = 'badge p-2 fs-6';
        if (status === 'Aberto') badge.classList.add('bg-primary');
        else if (status === 'Em Andamento') badge.classList.add('bg-warning', 'text-dark');
        else if (status === 'Concluído') badge.classList.add('bg-success');
        else badge.classList.add('bg-secondary');
    }

    // Initialize color
    setStatus('<?= $os['status'] ?>');

    // Auto-calculate Next Maintenance Date
    document.getElementById('validade_meses').addEventListener('change', function() {
        const months = parseInt(this.value);
        if (months > 0) {
            const today = new Date();
            today.setMonth(today.getMonth() + months);
            
            // Format to dd/mm/yyyy
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
            const yyyy = today.getFullYear();
            
            document.getElementById('data_proxima_manutencao').value = dd + '/' + mm + '/' + yyyy;
        } else {
             document.getElementById('data_proxima_manutencao').value = '';
        }
    });
</script>