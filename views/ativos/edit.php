<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Editar Ativo</h4>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/ativos/update" method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($ativo['id']) ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Ativo</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($ativo['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Proprietário (Cliente)</label>
                        <select class="form-select searchable" id="client_id" name="client_id">
                            <option value="">-- Selecione um Cliente (Opcional) --</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $client): ?>
                                    <option value="<?= $client['id'] ?>" 
                                        <?= $client['id'] == $ativo['client_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($ativo['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">UUID (Somente Leitura)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($ativo['uuid']) ?>" readonly>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Atualizar Ativo</button>
                        <a href="<?= BASE_URL ?>/ativos" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
