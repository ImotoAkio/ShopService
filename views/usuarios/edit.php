<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa-solid fa-user-pen me-2"></i> Editar Usuário</h5>
            </div>
            <div class="card-body">
                <form action="<?= \BASE_URL ?>/usuarios/update" method="POST">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="name" name="name" required
                            value="<?= htmlspecialchars($user['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required
                            value="<?= htmlspecialchars($user['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Função / Permissão</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : '' ?>>Funcionário
                            </option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administração</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Nova Senha (Opcional)</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Deixe em branco para manter a senha atual.</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= \BASE_URL ?>/usuarios" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-2"></i> Atualizar Usuário
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>