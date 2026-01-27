<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3><i class="fa-solid fa-users me-2"></i> Gestão de Clientes</h3>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= \BASE_URL ?>/clientes/criar" class="btn btn-primary">
            <i class="fa-solid fa-user-plus me-2"></i> Novo Cliente
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= \BASE_URL ?>/clientes" method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control"
                    placeholder="Buscar por Nome, Documento, Email ou Telefone..."
                    value="<?= htmlspecialchars($filter['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-search"></i> Buscar</button>
            </div>
            <!-- Preserve other params if needed, but usually search resets sort or keeps it? Let's keep simple. -->
        </form>
    </div>
</div>

<?php
function sortLink($col, $currentOrder, $currentDirection, $search)
{
    $minDirection = ($col === $currentOrder && $currentDirection === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($col === $currentOrder) {
        $icon = ($currentDirection === 'ASC') ? '<i class="fa-solid fa-sort-up"></i>' : '<i class="fa-solid fa-sort-down"></i>';
    } else {
        $icon = '<i class="fa-solid fa-sort text-muted" style="opacity: 0.3;"></i>';
    }

    $url = \BASE_URL . "/clientes?order_by=$col&direction=$minDirection&search=" . urlencode($search);
    return "<a href='$url' class='text-white text-decoration-none'>$icon </a> <a href='$url' class='text-white text-decoration-none'>$col</a>"; // Hacky text mix
}
// Actually, let's just make the whole TH clickable or the text inside.
function getSortHeader($label, $col, $filter)
{
    $currentOrder = $filter['order_by'];
    $currentDirection = $filter['direction'];
    $search = $filter['search'];

    $newDirection = ($col === $currentOrder && $currentDirection === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($col === $currentOrder) {
        $icon = ($currentDirection === 'ASC') ? ' <i class="fa-solid fa-sort-up"></i>' : ' <i class="fa-solid fa-sort-down"></i>';
    }

    $url = \BASE_URL . "/clientes?order_by=$col&direction=$newDirection&search=" . urlencode($search);
    return "<a href='$url' class='text-white text-decoration-none d-block'>$label $icon</a>";
}
?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col"><?= getSortHeader('# ID', 'id', $filter) ?></th>
                        <th scope="col"><?= getSortHeader('Nome', 'name', $filter) ?></th>
                        <th scope="col"><?= getSortHeader('Documento', 'documento', $filter) ?></th>
                        <th scope="col"><?= getSortHeader('Telefone', 'phone', $filter) ?></th>
                        <th scope="col"><?= getSortHeader('Email', 'email', $filter) ?></th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Nenhum cliente encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td><strong>
                                        <?= $c['id'] ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($c['name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['documento'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['phone']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['email']) ?>
                                </td>
                                <td>
                                    <a href="<?= \BASE_URL ?>/cliente/view/<?= $c['id'] ?>"
                                        class="btn btn-sm btn-info text-white me-2">
                                        <i class="fa-solid fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>