<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-file-import me-2"></i> Importar Orçamentos Antigos (.docx)</h3>
    <a href="<?= \BASE_URL ?>/orcamentos" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fa-solid fa-upload me-2"></i> Selecionar Arquivos</h5>
            </div>
            <div class="card-body">
                <div id="drop-zone"
                    class="border border-2 border-primary border-dashed rounded p-5 text-center bg-light"
                    style="cursor: pointer; transition: all 0.2s;">
                    <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-3"></i>
                    <p class="fs-5 mb-1">Arraste arquivos .docx aqui</p>
                    <p class="text-muted small">ou clique para selecionar</p>
                    <input type="file" id="file-input" name="files[]" multiple accept=".docx" class="d-none">
                </div>

                <div class="mt-3 d-grid">
                    <button id="btn-process" class="btn btn-success" disabled>
                        <i class="fa-solid fa-play me-2"></i> Iniciar Importação
                    </button>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i> <strong>Como funciona:</strong>
            <ul class="mb-0 mt-2 ps-3">
                <li>O sistema lerá o texto do arquivo Word.</li>
                <li>Enviará para a IA (Gemini) extrair os dados.</li>
                <li>Criará o Cliente (se não existir) e o Orçamento automaticamente.</li>
            </ul>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fa-solid fa-list-check me-2"></i> Progresso</h5>
                <span id="status-counter" class="badge bg-secondary">0 arquivos</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0" id="files-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 50%">Arquivo</th>
                                <th style="width: 20%">Tamanho</th>
                                <th style="width: 30%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="empty-row">
                                <td colspan="3" class="text-center text-muted py-5">
                                    Nenhum arquivo selecionado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light" id="console-area" style="display:none;">
                <pre id="log-output" class="mb-0 small text-muted"
                    style="max-height: 150px; overflow-y: auto; font-size: 0.75rem;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const filesTableBody = document.querySelector('#files-table tbody');
        const emptyRow = document.getElementById('empty-row');
        const btnProcess = document.getElementById('btn-process');
        const statusCounter = document.getElementById('status-counter');
        const logOutput = document.getElementById('log-output');
        const consoleArea = document.getElementById('console-area');

        let fileQueue = [];

        // Drag & Drop Events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-white');
            dropZone.classList.remove('bg-light');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-white');
            dropZone.classList.add('bg-light');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-white');
            dropZone.classList.add('bg-light');
            handleFiles(e.dataTransfer.files);
        });

        dropZone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files);
            fileInput.value = ''; // Reset to allow same file selection again
        });

        function handleFiles(files) {
            if (files.length > 0) {
                if (emptyRow) emptyRow.style.display = 'none';

                Array.from(files).forEach(file => {
                    if (file.name.endsWith('.docx') || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                        // Avoid duplicates
                        if (!fileQueue.some(f => f.name === file.name)) {
                            fileQueue.push(file);
                            addFileRow(file);
                        }
                    }
                });

                updateCounter();
                btnProcess.disabled = false;
            }
        }

        function addFileRow(file) {
            const row = document.createElement('tr');
            row.id = 'row-' + sanitizeId(file.name);
            row.innerHTML = `
            <td class="text-truncate" style="max-width: 200px;" title="${file.name}">
                <i class="fa-regular fa-file-word text-primary me-2"></i> ${file.name}
            </td>
            <td>${formatBytes(file.size)}</td>
            <td><span class="badge bg-secondary status-badge">Pendente</span></td>
        `;
            filesTableBody.appendChild(row);
        }

        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        }

        function sanitizeId(name) {
            return name.replace(/[^a-zA-Z0-9]/g, '_');
        }

        function updateCounter() {
            statusCounter.innerText = `${fileQueue.length} arquivos`;
        }

        function addLog(msg) {
            consoleArea.style.display = 'block';
            logOutput.innerText += `> ${msg}\n`;
            logOutput.scrollTop = logOutput.scrollHeight;
        }

        // Processing Logic
        btnProcess.addEventListener('click', async () => {
            btnProcess.disabled = true;
            addLog('Iniciando processamento...');

            let processedCount = 0;

            const delay = ms => new Promise(res => setTimeout(res, ms));

            for (const file of fileQueue) {
                const rowId = 'row-' + sanitizeId(file.name);
                const row = document.getElementById(rowId);
                const badge = row.querySelector('.status-badge');

                // Skip if already processed successfully (though we clear queue at end)
                if (badge.classList.contains('bg-success')) continue;

                badge.className = 'badge bg-primary status-badge';
                badge.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';

                let attempts = 0;
                let maxRetries = 5;
                let success = false;

                while (attempts < maxRetries && !success) {
                    try {
                        addLog(`Processando: ${file.name} (Tentativa ${attempts + 1})`);

                        // 1. Upload File
                        const formData = new FormData();
                        formData.append('file', file);

                        const uploadResp = await fetch('<?= \BASE_URL ?>/orcamentos/import/upload', {
                            method: 'POST',
                            body: formData
                        });

                        const uploadJson = await uploadResp.json();

                        if (!uploadJson.success) {
                            throw new Error(uploadJson.message || 'Erro no upload');
                        }

                        badge.className = 'badge bg-info text-dark status-badge';
                        badge.innerHTML = '<i class="fa-solid fa-brain"></i> IA Analisando...';

                        // 2. Process with AI
                        const processResp = await fetch('<?= \BASE_URL ?>/orcamentos/import/process', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ filename: uploadJson.filename })
                        });

                        const contentType = processResp.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            const text = await processResp.text();
                            throw new Error("Resposta inválida do servidor: " + text.substring(0, 50) + "...");
                        }

                        const processJson = await processResp.json();

                        if (processJson.success) {
                            badge.className = 'badge bg-success status-badge';
                            badge.innerHTML = '<i class="fa-solid fa-check"></i> Sucesso';
                            addLog(`[OK] ${file.name}: ${processJson.message}`);
                            success = true;
                        } else {
                            // Check for Rate Limit Error (429) in message
                            if (processJson.message.includes('429') || processJson.message.includes('quota') || processJson.message.includes('insufficient_quota')) {
                                throw new Error(processJson.message);
                            }
                            throw new Error(processJson.message || 'Erro na importação');
                        }

                    } catch (error) {
                        console.error(error);

                        if (error.message.includes('429') || error.message.includes('quota') || error.message.includes('insufficient_quota')) {
                            const waitTime = 45; // seconds
                            badge.className = 'badge bg-warning text-dark status-badge';
                            badge.innerHTML = `<i class="fa-solid fa-clock"></i> Aguardando ${waitTime}s...`;
                            addLog(`[LIMIT] ${error.message} - Aguardando ${waitTime}s...`);

                            await delay(waitTime * 1000);
                            attempts++;
                        } else {
                            badge.className = 'badge bg-danger status-badge';
                            badge.innerHTML = 'Erro';
                            badge.title = error.message;
                            addLog(`[ERRO] ${file.name}: ${error.message}`);
                            break; // Fatal error, move to next file
                        }
                    }
                }

                if (!success) {
                    badge.className = 'badge bg-danger status-badge';
                    badge.innerHTML = 'Falhou';
                }

                processedCount++;

                // Polite delay between successful files to avoid hitting burst limits
                if (success) {
                    addLog('Aguardando 10s para o próximo arquivo...');
                    await delay(10000);
                }
            }

            addLog('Processamento concluído.');
            btnProcess.innerText = 'Concluído';
            btnProcess.disabled = false;
            fileQueue = []; // Clear queue
        });
    });
</script>