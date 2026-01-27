<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-user-plus me-2"></i> Novo Usuário</h5>
            </div>
            <div class="card-body">
                <form action="<?= \BASE_URL ?>/usuarios/store" method="POST">

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Função / Permissão</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="employee" selected>Funcionário</option>
                            <option value="admin">Administração</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Senha de Acesso</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Defina uma senha inicial para o usuário.</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= \BASE_URL ?>/usuarios" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa-solid fa-save me-2"></i> Salvar Usuário
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>