<div class="kanban-card" id="os-<?= $os['id'] ?>" draggable="true" ondragstart="drag(event)" data-id="<?= $os['id'] ?>">
    <div class="d-flex justify-content-between">
        <strong>OS #
            <?= $os['id'] ?>
        </strong>
        <span class="badge bg-secondary">
            <?= $os['tipo'] ?? 'Execução' ?>
        </span>
    </div>
    <div class="mt-2">
        <strong>
            <?= htmlspecialchars($os['client_name']) ?>
        </strong>
    </div>
    <p class="text-truncate" title="<?= htmlspecialchars($os['relatorio']) ?>">
        <?= htmlspecialchars(substr($os['relatorio'], 0, 50)) ?>...
    </p>
    <div class="meta">
        <span>
            <?= date('d/m/Y', strtotime($os['created_at'])) ?>
        </span>
        <a href="<?= \BASE_URL ?>/os/edit/<?= $os['id'] ?>" class="btn btn-sm btn-outline-primary"
            style="padding: 0px 5px; font-size: 0.7rem;">Ver</a>
    </div>
</div>