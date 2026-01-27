<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Equipamento | Shop Service</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .public-header {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .timeline {
            border-left: 3px solid #e9ecef;
            padding-left: 1.5rem;
            position: relative;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            width: 1rem;
            height: 1rem;
            background: #fff;
            border: 3px solid #007bff;
            border-radius: 50%;
            position: absolute;
            left: -2.1rem;
            top: 0.25rem;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="public-header shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <h4 class="m-0"><i class="fa-solid fa-screwdriver-wrench me-2"></i> Shop Service</h4>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-light btn-sm">Painel Admin</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn btn-outline-light btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container mb-5">
        <div class="row">
            <!-- Asset Details -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">
                        <i class="fa-solid fa-box-open me-2"></i> Detalhes do Equipamento
                    </div>
                    <div class="card-body">
                        <h3 class="card-title text-primary fw-bold mb-3">
                            <?= htmlspecialchars($ativo['name']) ?>
                        </h3>
                        <p class="text-muted small mb-1">PROPRIETÁRIO</p>
                        <p class="fw-bold">
                            <?= !empty($ativo['client_name']) ? htmlspecialchars($ativo['client_name']) : 'Não informado' ?>
                        </p>

                        <p class="text-muted small mb-1">DESCRIÇÃO</p>
                        <p>
                            <?= nl2br(htmlspecialchars($ativo['description'])) ?>
                        </p>

                        <p class="text-muted small mb-1">ID DO SISTEMA (UUID)</p>
                        <code class="d-block bg-light p-2 rounded"><?= htmlspecialchars($ativo['uuid']) ?></code>
                    </div>
                </div>
            </div>

            <!-- History Timeline -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i> Histórico de Serviços
                    </div>
                    <div class="card-body">
                        <?php if (empty($historico)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clipboard-list fa-3x mb-3"></i>
                                <p>Nenhum serviço registrado para este equipamento ainda.</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline mt-3">
                                <?php foreach ($historico as $os): ?>
                                    <div class="timeline-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="fw-bold mb-1">Ordem de Serviço #
                                                    <?= $os['id'] ?>
                                                </h5>
                                                <span class="badge bg-secondary mb-2">
                                                    <?= htmlspecialchars($os['status']) ?>
                                                </span>
                                            </div>
                                            <span class="text-muted small">
                                                <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                            </span>
                                        </div>
                                        <div class="bg-light p-3 rounded mt-2">
                                            <?php if (!empty($os['defeito'])): ?>
                                                <p class="mb-1"><strong>Defeito:</strong>
                                                    <?= htmlspecialchars($os['defeito']) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($os['solucao'])): ?>
                                                <p class="mb-0"><strong>Solução:</strong>
                                                    <?= htmlspecialchars($os['solucao']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>