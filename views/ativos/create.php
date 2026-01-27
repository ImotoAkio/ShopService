<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Cadastrar Novo Ativo</h4>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/ativos/store" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Ativo</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Proprietário (Cliente)</label>
                        <select class="form-select searchable" id="client_id" name="client_id">
                            <option value="">-- Selecione um Cliente (Opcional) --</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $client): ?>
                                    <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Salvar Ativo</button>
                        <a href="<?= BASE_URL ?>/ativos" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>