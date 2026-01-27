<style>
    .kanban-board {
        display: flex;
        overflow-x: auto;
        gap: 15px;
        padding-bottom: 20px;
    }

    .kanban-column {
        flex: 0 0 300px;
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        height: min-content;
        min-height: 400px;
        /* Drop target area */
    }

    .kanban-header {
        font-weight: bold;
        text-align: center;
        padding: 10px;
        color: white;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .header-aberto {
        background-color: #6c757d;
    }

    .header-emandamento {
        background-color: #007bff;
    }

    .header-aguardando {
        background-color: #ffc107;
        color: #333;
    }

    .header-concluido {
        background-color: #28a745;
    }

    .kanban-card {
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 10px;
        cursor: move;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .kanban-card h5 {
        font-size: 1rem;
        margin-bottom: 5px;
    }

    .kanban-card p {
        font-size: 0.85rem;
        margin-bottom: 5px;
        color: #666;
    }

    .kanban-card .meta {
        font-size: 0.75rem;
        color: #999;
        display: flex;
        justify-content: space-between;
    }

    .dragging {
        opacity: 0.5;
    }
</style>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Quadro Kanban de Ordens de Serviço</h2>

    <div class="kanban-board">
        <!-- Aberto -->
        <div class="kanban-column" data-status="Aberto" ondrop="drop(event)" ondragover="allowDrop(event)">
            <div class="kanban-header header-aberto">Aberto (
                <?= count($kanbanData['Aberto']) ?>)
            </div>
            <?php foreach ($kanbanData['Aberto'] as $os): ?>
                <?php include __DIR__ . '/card.php'; ?>
            <?php endforeach; ?>
        </div>

        <!-- Em Andamento -->
        <div class="kanban-column" data-status="Em Andamento" ondrop="drop(event)" ondragover="allowDrop(event)">
            <div class="kanban-header header-emandamento">Em Andamento (
                <?= count($kanbanData['Em Andamento']) ?>)
            </div>
            <?php foreach ($kanbanData['Em Andamento'] as $os): ?>
                <?php include __DIR__ . '/card.php'; ?>
            <?php endforeach; ?>
        </div>

        <!-- Aguardando Peças -->
        <div class="kanban-column" data-status="Aguardando Peças" ondrop="drop(event)" ondragover="allowDrop(event)">
            <div class="kanban-header header-aguardando">Aguardando Peças (
                <?= count($kanbanData['Aguardando Peças']) ?>)
            </div>
            <?php foreach ($kanbanData['Aguardando Peças'] as $os): ?>
                <?php include __DIR__ . '/card.php'; ?>
            <?php endforeach; ?>
        </div>

        <!-- Concluído -->
        <div class="kanban-column" data-status="Concluído" ondrop="drop(event)" ondragover="allowDrop(event)">
            <div class="kanban-header header-concluido">Concluído (
                <?= count($kanbanData['Concluído']) ?>)
            </div>
            <?php foreach ($kanbanData['Concluído'] as $os): ?>
                <?php include __DIR__ . '/card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
        ev.target.classList.add('dragging');
    }

    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        var card = document.getElementById(data);
        card.classList.remove('dragging');

        // Find closest column
        var column = ev.target.closest('.kanban-column');
        if (column) {
            column.appendChild(card);

            var newStatus = column.getAttribute('data-status');
            var osId = card.getAttribute('data-id');

            updateStatus(osId, newStatus);
        }
    }

    function updateStatus(osId, status) {
        fetch('<?= \BASE_URL ?>/kanban/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                os_id: osId,
                status: status
            })
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro ao atualizar status: ' + (data.message || 'Desconhecido'));
                    location.reload(); // Revert on error
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('Erro de conexão.');
                location.reload();
            });
    }

    // Cleanup dragging class if drag ends without drop
    document.addEventListener("dragend", function (event) {
        if (event.target.classList.contains("kanban-card")) {
            event.target.classList.remove("dragging");
        }
    });
</script>