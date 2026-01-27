<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-users me-2"></i> Gestão de Usuários</h3>
    <a href="<?= \BASE_URL ?>/usuarios/criar" class="btn btn-primary">
        <i class="fa-solid fa-user-plus me-2"></i> Novo Usuário
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col"># ID</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Email</th>
                        <th scope="col">Função</th>
                        <th scope="col">Cadastro</th>
                        <th scope="col" class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong>
                                        <?= $u['id'] ?>
                                    </strong></td>
                                <td>
                                    <?= htmlspecialchars($u['name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($u['email']) ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Administração</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Funcionário</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= \BASE_URL ?>/usuarios/edit/<?= $u['id'] ?>"
                                        class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="<?= \BASE_URL ?>/usuarios/delete/<?= $u['id'] ?>"
                                            class="btn btn-sm btn-outline-danger" title="Excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Você não pode se excluir"><i
                                                class="fa-solid fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>