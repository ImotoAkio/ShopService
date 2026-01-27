<h2 class="mb-4">Lista de Ativos</h2>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Nome</th>
                <th>UUID</th>
                <th>QR Code</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ativos)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhum ativo cadastrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ativos as $ativo): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($ativo['id']) ?>
                        </td>
                        <td>
                            <?= !empty($ativo['client_name']) ? htmlspecialchars($ativo['client_name']) : '<span class="text-muted">-</span>' ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($ativo['name']) ?>
                        </td>
                        <td><code><?= htmlspecialchars($ativo['uuid']) ?></code></td>
                        <td class="text-center">
                            <div id="qrcode-<?= $ativo['id'] ?>" class="qr-code"></div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    new QRCode(document.getElementById("qrcode-<?= $ativo['id'] ?>"), {
                                        text: "<?= BASE_URL ?>/ativo/view/<?= $ativo['uuid'] ?>",
                                        width: 64,
                                        height: 64
                                    });
                                });
                            </script>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/ativos/detalhes/<?= $ativo['id'] ?>" class="btn btn-sm btn-info text-white"
                                title="Ver Detalhes"><i class="fa-solid fa-eye"></i></a>

                            <a href="<?= BASE_URL ?>/ativos/edit/<?= $ativo['id'] ?>" class="btn btn-sm btn-warning text-dark"
                                title="Editar"><i class="fa-solid fa-pen"></i></a>

                            <form action="<?= BASE_URL ?>/ativos/delete" method="POST" class="d-inline"
                                onsubmit="return confirm('Tem certeza que deseja excluir este ativo?');">
                                <input type="hidden" name="id" value="<?= $ativo['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir"><i
                                        class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<a href="<?= BASE_URL ?>/ativos/criar" class="btn btn-primary">Novo Ativo</a>