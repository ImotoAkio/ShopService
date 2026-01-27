<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa-solid fa-user-plus me-2"></i> Novo Cliente</h5>
            </div>
            <div class="card-body">
                <form action="<?= \BASE_URL ?>/clientes/store" method="POST">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Nome Completo / Razão Social</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="documento" class="form-label">CPF / CNPJ</label>
                            <input type="text" class="form-control" id="documento" name="documento">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telefone / WhatsApp</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="responsavel" class="form-label">Nome do Responsável (Geral)</label>
                            <input type="text" class="form-control" id="responsavel" name="responsavel">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefone2" class="form-label">Telefone 2 (Geral)</label>
                            <input type="text" class="form-control" id="telefone2" name="telefone2">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cnpj" class="form-label">CNPJ (Específico)</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-secondary">Zelador</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="zelador_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="zelador_nome" name="zelador_nome">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zelador_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="zelador_email" name="zelador_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zelador_tel" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="zelador_tel" name="zelador_tel">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zelador_tel2" class="form-label">Telefone 2</label>
                            <input type="text" class="form-control" id="zelador_tel2" name="zelador_tel2">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-secondary">Síndico</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sindico_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="sindico_nome" name="sindico_nome">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sindico_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="sindico_email" name="sindico_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sindico_tel" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="sindico_tel" name="sindico_tel">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sindico_tel2" class="form-label">Telefone 2</label>
                            <input type="text" class="form-control" id="sindico_tel2" name="sindico_tel2">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-secondary">Administradora</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_nome" class="form-label">Nome/Razão Social</label>
                            <input type="text" class="form-control" id="admin_nome" name="admin_nome">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="admin_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="admin_tel" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="admin_tel" name="admin_tel">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="admin_tel2" class="form-label">Telefone 2</label>
                            <input type="text" class="form-control" id="admin_tel2" name="admin_tel2">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label">Endereço Completo</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= \BASE_URL ?>/clientes" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa-solid fa-save me-2"></i> Salvar Cliente
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>