<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= \BASE_URL ?>/galeria">Galeria</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($client['name']) ?>
                </li>
            </ol>
        </nav>
        <h3><i class="fa-solid fa-images me-2"></i> Galeria do Cliente</h3>
    </div>
    <a href="<?= \BASE_URL ?>/clientes/editar/<?= $client['id'] ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-user-edit me-2"></i> Ver Perfil
    </a>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="galleryTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="installation-tab" data-bs-toggle="tab" data-bs-target="#installation"
            type="button" role="tab">
            <i class="fa-solid fa-tools me-2"></i> Instalação (Principais)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"
            role="tab">
            <i class="fa-solid fa-history me-2"></i> Histórico de Serviços (OS)
        </button>
    </li>
</ul>

<div class="tab-content" id="galleryTabContent">

    <!-- Tab 1: Installation Photos -->
    <div class="tab-pane fade show active" id="installation" role="tabpanel">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Adicionar Fotos da Instalação</h6>
            </div>
            <div class="card-body">
                <form action="<?= \BASE_URL ?>/galeria/upload" method="POST" enctype="multipart/form-data"
                    class="d-flex gap-2">
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                    <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" required>
                    <input type="text" name="description" class="form-control" placeholder="Descrição (Opcional)">
                    <button type="submit" class="btn btn-success text-nowrap">
                        <i class="fa-solid fa-upload me-2"></i> Enviar
                    </button>
                </form>
            </div>
        </div>

        <div class="row g-3">
            <?php if (empty($installationPhotos)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fa-regular fa-image fa-3x mb-3"></i>
                    <p>Nenhuma foto de instalação cadastrada.</p>
                </div>
            <?php else: ?>
                <?php foreach ($installationPhotos as $photo): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 shadow-sm">
                            <a href="#"
                                onclick="openEditor('client', <?= $photo['id'] ?>, '<?= \ASSET_URL . $photo['photo_path'] ?>', '<?= htmlspecialchars($photo['description'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($photo['observacoes'] ?? '', ENT_QUOTES) ?>'); return false;">
                                <img src="<?= \ASSET_URL . $photo['photo_path'] ?>" class="card-img-top"
                                    style="height: 200px; object-fit: cover;" alt="Instalação">
                            </a>
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted text-truncate" title="<?= htmlspecialchars($photo['description']) ?>">
                                    <?= !empty($photo['description']) ? htmlspecialchars($photo['description']) : 'Sem descrição' ?>
                                </small>
                                <a href="<?= \BASE_URL ?>/galeria/delete/<?= $photo['id'] ?>"
                                    class="btn btn-sm btn-outline-danger text-danger border-0"
                                    onclick="return confirm('Tem certeza que deseja excluir esta foto?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                            <div class="card-footer p-1 text-end bg-white border-0">
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    <?= date('d/m/Y H:i', strtotime($photo['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab 2: Service History (OS Folders) -->
    <div class="tab-pane fade" id="history" role="tabpanel">
        <?php if (empty($osList)): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="fa-solid fa-folder-open fa-3x mb-3"></i>
                <p>Nenhum serviço com fotos encontrado.</p>
            </div>
        <?php else: ?>
            <div class="accordion" id="accordionOS">
                <?php foreach ($osList as $index => $os): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $os['id'] ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse<?= $os['id'] ?>">
                                <i class="fa-solid fa-folder me-2 text-warning"></i>
                                <strong>OS #
                                    <?= $os['id'] ?>
                                </strong>
                                <span class="mx-2 text-muted">|</span>
                                <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                <span class="mx-2 text-muted">|</span>
                                <span class="badge bg-secondary">
                                    <?= $os['count'] ?> fotos
                                </span>
                            </button>
                        </h2>
                        <div id="collapse<?= $os['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionOS">
                            <div class="accordion-body">
                                <div class="row g-2">
                                    <?php
                                    // Fetch photos for this OS inline (efficient enough for small scale)
                                    // Ideally fetched in Controller and grouped, but this works for prototype.
                                    $db = \App\Core\Database::getInstance()->getConnection();
                                    $stmtP = $db->prepare("SELECT * FROM os_fotos WHERE os_id = ?");
                                    $stmtP->execute([$os['id']]);
                                    $osPhotos = $stmtP->fetchAll();
                                    ?>
                                    <?php foreach ($osPhotos as $osp): ?>
                                        <div class="col-md-2 col-4">
                                            <a href="#"
                                                onclick="openEditor('os', <?= $osp['id'] ?>, '<?= \ASSET_URL . $osp['photo_path'] ?>', '<?= htmlspecialchars($osp['description'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($osp['observacoes'] ?? '', ENT_QUOTES) ?>'); return false;">
                                                <img src="<?= \ASSET_URL . $osp['photo_path'] ?>" class="img-thumbnail"
                                                    style="height: 100px; width: 100%; object-fit: cover;">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3 text-end">
                                    <a href="<?= \BASE_URL ?>/os/pdf/<?= $os['id'] ?>" target="_blank"
                                        class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-file-pdf me-1"></i> Ver PDF da OS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Image Editor Modal -->
<div class="modal fade" id="imageEditorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i> Editor de Imagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Canvas Area -->
                    <div class="col-lg-9 bg-light d-flex justify-content-center align-items-center position-relative"
                        style="min-height: 500px; overflow: hidden;">
                        <div id="editor-loading" style="display: none; flex-direction: column; align-items: center;">
                            <div class="spinner-border text-primary" role="status"></div>
                            <span class="mt-2 text-muted">Carregando imagem...</span>
                        </div>
                        <div id="editor-content">
                            <canvas id="editor-canvas"></canvas>
                        </div>

                        <!-- Canvas Toolbar -->
                        <div
                            class="position-absolute bottom-0 start-0 w-100 p-2 d-flex justify-content-center gap-2 bg-dark bg-opacity-75">
                            <button class="btn btn-sm btn-light" onclick="zoomIn()"><i
                                    class="fa-solid fa-search-plus"></i></button>
                            <button class="btn btn-sm btn-light" onclick="zoomOut()"><i
                                    class="fa-solid fa-search-minus"></i></button>
                            <button class="btn btn-sm btn-outline-light" id="btn-draw" onclick="toggleDrawing()"><i
                                    class="fa-solid fa-pencil"></i> Desenhar</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="clearCanvas()"><i
                                    class="fa-solid fa-eraser"></i> Limpar Desenhos</button>
                        </div>
                    </div>

                    <!-- Sidebar Details -->
                    <div class="col-lg-3 p-3 border-start">
                        <h6 class="border-bottom pb-2 mb-3">Detalhes da Foto</h6>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Descrição</label>
                            <input type="text" id="editor-description" class="form-control form-control-sm">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Observações</label>
                            <textarea id="editor-obs" class="form-control form-control-sm" rows="5"></textarea>
                        </div>

                        <div class="mt-auto d-grid">
                            <button id="btn-save-all" class="btn btn-primary" onclick="saveChanges()">
                                <i class="fa-solid fa-save me-2"></i> Salvar Tudo
                            </button>
                            <small class="text-muted text-center mt-2" style="font-size: 0.7rem;">
                                Salvar atualizará a imagem e os textos.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>