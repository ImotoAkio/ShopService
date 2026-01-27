<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Service - Painel</title>
    <!-- Define CONSTANTS if not defined (fallback) -->
    <?php if (!defined('BASE_URL')) {
        define('BASE_URL', '');
    } ?>
    <?php if (!defined('ASSET_URL')) {
        define('ASSET_URL', '');
    } ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/assets/css/dashboard.css">
</head>

<body>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <!-- Sidebar Heading with Logo -->
            <div class="sidebar-heading text-center py-4" style="height: auto;">
                <img src="<?= ASSET_URL ?>/assets/img/logo.png" alt="Shop Service" class="img-fluid"
                    style="max-height: 80px;">
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= BASE_URL ?>/dashboard" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-gauge me-2"></i> Dashboard
                </a>
                <a href="<?= BASE_URL ?>/ativos" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-boxes-stacked me-2"></i> Ativos
                </a>

                <a href="<?= BASE_URL ?>/os" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-file-circle-plus me-2"></i> Ordens de Serviço
                </a>
                <a href="<?= BASE_URL ?>/clientes" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-address-book me-2"></i> Clientes
                </a>
                <a href="<?= BASE_URL ?>/orcamentos" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i> Orçamentos
                </a>
                <a href="<?= BASE_URL ?>/orcamentos/import" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-file-import me-2"></i> Migração (Importar)
                </a>
                <a href="<?= BASE_URL ?>/valves" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-faucet me-2"></i> Modelos VRP
                </a>
                <a href="<?= BASE_URL ?>/financeiro" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-chart-pie me-2"></i> Financeiro
                </a>
                <a href="<?= BASE_URL ?>/galeria" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-images me-2"></i> Galeria
                </a>
                <a href="<?= BASE_URL ?>/kanban" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-list-check me-2"></i> Quadro Kanban
                </a>
                <a href="<?= BASE_URL ?>/usuarios" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-users me-2"></i> Usuários
                </a>
                <a href="<?= BASE_URL ?>/configuracoes" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-cog me-2"></i> Configurações
                </a>
                <a href="<?= BASE_URL ?>/logout"
                    class="list-group-item list-group-item-action mt-4 text-danger-emphasis">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Sair
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">

            <!-- Header -->
            <nav class="dashboard-header">
                <div class="header-left">
                    <button class="btn btn-link link-dark" id="sidebarToggle">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <!-- Realtime Clock -->
                    <div id="realtime-clock" class="ms-3 fw-bold text-muted"></div>
                </div>

                <div class="header-right">
                    <!-- Fullscreen Button -->
                    <button id="btnFullscreen" class="btn-fullscreen" title="Tela Cheia">
                        <i class="fa-solid fa-expand"></i>
                    </button>

                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark"
                            id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user-circle fa-xl me-2"></i>
                            <strong><?= $_SESSION['user_name'] ?? 'Usuário' ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout">Sair</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid px-4 py-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php include $viewContent; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- Summernote CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script src="<?= ASSET_URL ?>/assets/js/dashboard.js"></script>
    <script src="<?= ASSET_URL ?>/assets/js/image-editor.js"></script>

    <script>
        // Auto-initialize Tom Select for elements with class 'searchable'
        document.querySelectorAll('.searchable').forEach((el) => {
            new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>
</body>

</html>