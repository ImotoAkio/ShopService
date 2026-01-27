<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-faucet me-2"></i> Modelos de VRP</h2>
    <a href="<?= \BASE_URL ?>/valves/create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-2"></i> Novo Modelo
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 25%;">Nome</th>
                        <th style="width: 50%;">Resumo Descrição</th>
                        <th style="width: 20%;" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($valves)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                Nenhum modelo cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($valves as $valve): ?>
                            <tr>
                                <td>
                                    <?= $valve['id'] ?>
                                </td>
                                <td class="fw-bold">
                                    <?= htmlspecialchars($valve['name']) ?>
                                </td>
                                <td class="text-muted text-truncate" style="max-width: 300px;">
                                    <?= htmlspecialchars(substr($valve['service_description'], 0, 100)) ?>...
                                </td>
                                <td class="text-end">
                                    <a href="<?= \BASE_URL ?>/valves/edit/<?= $valve['id'] ?>"
                                        class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="<?= \BASE_URL ?>/valves/delete" method="POST" class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja excluir este modelo?');">
                                        <input type="hidden" name="id" value="<?= $valve['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>