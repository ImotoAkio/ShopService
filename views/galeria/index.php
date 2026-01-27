<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3><i class="fa-solid fa-images me-2"></i> Galeria de Fotos por Cliente</h3>
        <p class="text-muted">Acesse as fotos de instalação e histórico de serviços de cada cliente.</p>
    </div>
    <div class="col-md-4">
        <form action="<?= \BASE_URL ?>/galeria" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Buscar cliente..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($clientes)): ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="fa-solid fa-user-slash fa-3x mb-3"></i>
            <h5>Nenhum cliente encontrado.</h5>
        </div>
    <?php else: ?>
        <?php foreach ($clientes as $client): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 folder-card">
                    <div class="card-body text-center">
                        <div class="folder-icon mb-3 text-primary">
                            <i class="fa-solid fa-folder-open fa-3x"></i>
                        </div>
                        <h5 class="card-title text-truncate">
                            <?= htmlspecialchars($client['name']) ?>
                        </h5>
                        <p class="card-text text-muted small">
                            <i class="fa-solid fa-image me-1"></i>
                            <?= $client['photo_count'] ?> Fotos de Instalação<br>
                            <i class="fa-solid fa-briefcase me-1"></i>
                            <?= $client['os_photo_count'] ?> OS com Fotos
                        </p>
                        <a href="<?= \BASE_URL ?>/galeria/show/<?= $client['id'] ?>"
                            class="btn btn-primary w-100 stretched-link">
                            Acessar Galeria
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .folder-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .folder-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }
</style>