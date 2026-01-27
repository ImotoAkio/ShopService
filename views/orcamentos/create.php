<?php
// Ensure $valve_models is available, if not, empty array to prevent error
$valve_models = $valve_models ?? [];
?>
<link rel="stylesheet" href="<?= \ASSET_URL ?>/assets/css/accessibility.css">

<div class="row">
    <!-- Left Column: Form -->
    <div class="col-lg-7 col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Novo Orçamento</h5>
                <span class="badge bg-light text-dark">Data: <?= date('d/m/Y') ?></span>
            </div>
            <div class="card-body">
                <form action="<?= \BASE_URL ?>/orcamentos/store" method="POST" id="orcamentoForm">

                    <!-- Cliente -->
                    <div class="mb-4">
                        <label for="client_id" class="form-label">Cliente</label>
                        <select class="form-select form-select-lg searchable" id="client_id" name="client_id" required>
                            <option value="" selected disabled>Selecione um cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" data-name="<?= htmlspecialchars($cliente['name']) ?>"
                                    data-address="<?= htmlspecialchars($cliente['address']) ?>"
                                    data-doc="<?= htmlspecialchars($cliente['documento']) ?>"
                                    data-cnpj="<?= htmlspecialchars($cliente['cnpj']) ?>">
                                    <?= htmlspecialchars($cliente['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted fs-6 mb-2">
                            <i class="fa-solid fa-arrow-up"></i> Escolha o cliente na lista acima.
                        </div>
                    </div>

                    <!-- OS Link (Optional) -->
                    <div class="mb-3">
                        <label for="os_id" class="form-label">Vincular Ordem de Serviço (Opcional)</label>
                        <select class="form-select searchable" id="os_id" name="os_id">
                            <option value="">-- Sem Vínculo --</option>
                            <?php foreach ($oss as $os): ?>
                                <option value="<?= $os['id'] ?>" data-client-id="<?= $os['client_id'] ?>">
                                    OS #<?= $os['id'] ?> - <?= htmlspecialchars($os['client_name']) ?>
                                    [<?= $os['status'] ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tipo de Orçamento -->
                    <div class="card bg-light mb-4 border-info">
                        <div class="card-body">
                            <label class="form-label fw-bold d-block mb-3">Tipo de Orçamento</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="orcamento_tipo" id="tipo_vrp"
                                        value="vrp">
                                    <label class="form-check-label fw-bold" for="tipo_vrp">
                                        <i class="fa-solid fa-faucet me-1"></i> Template VRP
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="orcamento_tipo" id="tipo_custom"
                                        value="custom" checked>
                                    <label class="form-check-label fw-bold" for="tipo_custom">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Personalizado
                                    </label>
                                </div>
                            </div>
                            <div class="form-text mt-3 text-dark">
                                <i class="fa-solid fa-circle-info"></i> Escolha <strong>"Template VRP"</strong> para
                                preencher automaticamente ou <strong>"Personalizado"</strong> para escrever do zero.
                            </div>

                            <!-- VRP Model Selector (Hidden by default) -->
                            <div id="vrp_model_container" class="mt-3 d-none">
                                <label for="valve_model_id" class="form-label text-primary">Selecione o Modelo da
                                    Válvula:</label>
                                <div class="input-group">
                                    <select class="form-select form-select-lg border-primary searchable"
                                        id="valve_model_id">
                                        <option value="">-- Escolha um Modelo --</option>
                                        <?php foreach ($valve_models as $vm): ?>
                                            <option value="<?= $vm['id'] ?>">
                                                <?= htmlspecialchars($vm['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-info" id="btn-add-block"
                                        data-bs-toggle="modal" data-bs-target="#modalVRPDetails">
                                        <i class="fa-solid fa-plus-circle me-1"></i> Adicionar Bloco de Serviço
                                    </button>
                                </div>
                                <div class="form-text">Selecione o modelo e clique em "Adicionar Bloco" para incluir no
                                    orçamento (é possível adicionar múltiplos blocos).</div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Orçamento -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white fw-bold">Detalhes da Proposta</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="assunto" class="form-label">Assunto</label>
                                <input type="text" class="form-control" id="assunto" name="assunto"
                                    placeholder="Ex: Manutenção das VRP"
                                    value="<?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['assunto']) : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label for="servico_descricao" class="form-label">Descrição Geral do Serviço</label>
                                <textarea class="form-control" id="servico_descricao" name="servico_descricao"
                                    rows="3"><?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['servico_descricao']) : '' ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="procedimentos" class="form-label">Procedimentos</label>
                                <textarea class="form-control" id="procedimentos" name="procedimentos"
                                    rows="8"><?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['procedimentos']) : '' ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="duracao" class="form-label">Duração</label>
                                    <input type="text" class="form-control" id="duracao" name="duracao"
                                        placeholder="Ex: 01 dia"
                                        value="<?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['duracao']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="validade" class="form-label">Validade</label>
                                    <input type="text" class="form-control" id="validade" name="validade"
                                        value="<?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['validade']) : '10 dias' ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="garantia" class="form-label">Garantia</label>
                                    <select class="form-select" id="garantia" name="garantia">
                                        <option value="03 (três) meses" <?= (isset($base_orcamento) && $base_orcamento['garantia'] == '03 (três) meses') ? 'selected' : '' ?>>03
                                            (três) meses</option>
                                        <option value="06 (seis) meses" <?= (!isset($base_orcamento) || $base_orcamento['garantia'] == '06 (seis) meses') ? 'selected' : '' ?>>06
                                            (seis) meses</option>
                                        <option value="12 (doze) meses" <?= (isset($base_orcamento) && $base_orcamento['garantia'] == '12 (doze) meses') ? 'selected' : '' ?>>12
                                            (doze) meses</option>
                                        <option value="24 (vinte e quatro) meses" <?= (isset($base_orcamento) && $base_orcamento['garantia'] == '24 (vinte e quatro) meses') ? 'selected' : '' ?>>24 (vinte e quatro) meses</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="forma_pagamento" class="form-label">Pagamento</label>
                                    <input type="text" class="form-control" id="forma_pagamento" name="forma_pagamento"
                                        placeholder="Ex: Boleto 28dd"
                                        value="<?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['forma_pagamento']) : '' ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="observacoes" class="form-label">Observações</label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-add-parts">
                                        <i class="fa-solid fa-list-ul"></i> Inserir Peças Opcionais
                                    </button>
                                </div>
                                <textarea class="form-control" id="observacoes" name="observacoes"
                                    rows="8"><?= isset($base_orcamento) ? htmlspecialchars($base_orcamento['observacoes']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Itens do Orçamento -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-success">Itens / Custos</label>
                            <button type="button" class="btn btn-sm btn-success" id="btn-add-item">
                                <i class="fa-solid fa-plus me-1"></i> Adicionar Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="itens-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 50%;">Descrição</th>
                                        <th style="width: 15%;">Qtd</th>
                                        <th style="width: 20%;">Valor (R$)</th>
                                        <th style="width: 15%;">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="itens-body"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold fs-5">TOTAL:</td>
                                        <td class="fw-bold fs-5 text-success">R$ <span id="total-geral">0.00</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                        <a href="<?= \BASE_URL ?>/orcamentos"
                            class="btn btn-outline-secondary btn-lg-acc me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg-acc">
                            <i class="fa-solid fa-save me-2"></i> Salvar Orçamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Preview -->
    <div class="col-lg-5 d-none d-lg-block bg-dark p-3">
        <h5 class="text-white mb-3"><i class="fa-solid fa-eye me-2"></i> Pré-visualização do Documento</h5>
        <div class="preview-container">
            <div class="preview-paper" id="live-preview">
                <!-- Header -->
                <div class="header d-flex align-items-center mb-4">
                    <img src="<?= \ASSET_URL ?>/assets/img/logo.jpg" alt="Logo"
                        style="max-width: 150px; margin-right: 20px;">
                    <div style="font-size: 9pt;">
                        <strong>MIK – SERVIÇOS HIDRAULICOS LTDA</strong><br>
                        Av dos Imarés, 1383 – Indianópolis – SP<br>
                        Tel: (11) 5579-0835<br>
                        shopservicevalvularedutora.com.br
                    </div>
                </div>

                <div class="text-end mb-4">
                    Orç. PREVIEW/<?= date('Y') ?><br>
                    São Paulo, <?= date('d/m/Y') ?>.
                </div>

                <div class="content-block mb-3">
                    <strong id="prev_client_name">[Nome do Cliente]</strong><br>
                    <span id="prev_client_address">[Endereço]</span><br>
                    <strong>CNPJ: <span id="prev_client_doc">[Documento]</span></strong>
                </div>

                <div class="content-block mb-3">
                    A/C<br>
                    Sr(a). Responsável
                </div>

                <div class="section-title">Assunto: <span id="prev_assunto" class="fw-normal">[Assunto]</span></div>

                <div class="content-block mb-3">
                    Prezado Senhor;<br>
                    Conforme solicitação de VS temos a satisfação em colocar a disposição os nossos conhecimentos
                    técnicos.
                </div>

                <div class="section-title">Serviço:</div>
                <div class="content-block mb-3" id="prev_servico">...</div>

                <div class="section-title">Procedimentos:</div>
                <div class="content-block mb-3" id="prev_procedimentos">...</div>

                <div class="section-title">Duração:</div>
                <div class="content-block" id="prev_duracao">...</div>

                <div class="section-title">Custo: (Validade: <span id="prev_validade">...</span>)</div>
                <div class="content-block fw-bold fs-5">
                    Total ........................................................ R$ <span id="prev_total">0,00</span>
                </div>

                <div class="content-block highlight p-1 mt-2">
                    Garantia de <span id="prev_garantia">...</span>...
                </div>

                <div class="section-title mt-3">Observações:</div>
                <div class="content-block" id="prev_obs">...</div>

                <div class="section-title mt-3">Forma de Pagamento:</div>
                <div class="content-block" id="prev_pagamento">...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal VRP Details (Token Replacer) -->
<div class="modal fade" id="modalVRPDetails" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Válvula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formVRPDetails">
                    <div class="mb-2">
                        <label>Quantidade</label>
                        <input type="text" class="form-control vrp-token" data-token="[QTD]"
                            placeholder="Ex: 02 (duas)">
                    </div>
                    <div class="mb-2">
                        <label>Modelo</label>
                        <input type="text" class="form-control vrp-token" data-token="[MODELO]"
                            placeholder="Ex: 420 flangeadas">
                    </div>
                    <div class="mb-2">
                        <label>Local</label>
                        <input type="text" class="form-control vrp-token" data-token="[LOCAL]"
                            placeholder="Ex: no shaft">
                    </div>
                    <div class="mb-2">
                        <label>Torre</label>
                        <input type="text" class="form-control vrp-token" data-token="[TORRE]"
                            placeholder="Ex: Torre A">
                    </div>
                    <div class="mb-2">
                        <label>PDS</label>
                        <input type="text" class="form-control vrp-token" data-token="[PDS]" placeholder="Ex: 3 kgf">
                    </div>
                    <div class="mb-2">
                        <label>Alcance</label>
                        <input type="text" class="form-control vrp-token" data-token="[ALCANCE]"
                            placeholder="Ex: 10o andar">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btn-apply-vrp-details">Aplicar ao Texto</button>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP Data to JS -->
<script>
    const VALVE_MODELS = <?= json_encode($valve_models) ?>;
    const ITEMS_DATA = <?= json_encode($base_itens ?? []) ?>;
    const BASE_CLIENT_ID = <?= isset($base_orcamento['client_id']) ? $base_orcamento['client_id'] : 'null' ?>;
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Elements ---
        const form = document.getElementById('orcamentoForm');


        // Inputs
        const clientSelect = document.getElementById('client_id');
        const assuntoInput = document.getElementById('assunto');
        const servicoInput = document.getElementById('servico_descricao');
        const procedInput = document.getElementById('procedimentos');
        const duracaoInput = document.getElementById('duracao');
        const validadeInput = document.getElementById('validade');
        const garantiaInput = document.getElementById('garantia');
        const obsInput = document.getElementById('observacoes');
        const pagInput = document.getElementById('forma_pagamento');

        // Preview Elements
        const prevClientName = document.getElementById('prev_client_name');
        const prevClientEnd = document.getElementById('prev_client_address');
        const prevClientDoc = document.getElementById('prev_client_doc');
        const prevAssunto = document.getElementById('prev_assunto');
        const prevServico = document.getElementById('prev_servico');
        const prevProced = document.getElementById('prev_procedimentos');
        const prevDuracao = document.getElementById('prev_duracao');
        const prevValidade = document.getElementById('prev_validade');
        const prevTotal = document.getElementById('prev_total');
        const prevGarantia = document.getElementById('prev_garantia');
        const prevObs = document.getElementById('prev_obs');
        const prevPag = document.getElementById('prev_pagamento');

        // Logic Elements
        const radioVRP = document.getElementById('tipo_vrp');
        const radioCustom = document.getElementById('tipo_custom');
        const vrpContainer = document.getElementById('vrp_model_container');
        const valveSelect = document.getElementById('valve_model_id');
        const btnApplyDetails = document.getElementById('btn-apply-vrp-details');

        // --- Live Preview Updaters ---
        function updatePreview() {
            // Client
            const selectedClient = clientSelect.options[clientSelect.selectedIndex];
            if (selectedClient && !selectedClient.disabled) {
                prevClientName.textContent = selectedClient.dataset.name || '';
                prevClientEnd.textContent = selectedClient.dataset.address || '';
                prevClientDoc.textContent = selectedClient.dataset.cnpj || selectedClient.dataset.doc || '';
            }

            prevAssunto.textContent = assuntoInput.value;
            prevServico.innerHTML = nl2br(servicoInput.value);
            prevProced.innerHTML = nl2br(procedInput.value);
            prevDuracao.textContent = duracaoInput.value;
            prevValidade.textContent = validadeInput.value;
            prevGarantia.textContent = garantiaInput.value;
            prevObs.innerHTML = nl2br(obsInput.value);
            prevPag.textContent = pagInput.value;
        }

        function nl2br(str) {
            return str ? str.replace(/\n/g, '<br>') : '';
        }

        // Attach Listeners
        const allInputs = [clientSelect, assuntoInput, servicoInput, procedInput, duracaoInput, validadeInput, garantiaInput, obsInput, pagInput];
        allInputs.forEach(el => el.addEventListener('input', updatePreview));
        allInputs.forEach(el => el.addEventListener('change', updatePreview));

        // --- VRP Logic ---
        function toggleVRP() {
            if (radioVRP.checked) {
                vrpContainer.classList.remove('d-none');
            } else {
                vrpContainer.classList.add('d-none');
            }
        }
        radioVRP.addEventListener('change', toggleVRP);
        radioCustom.addEventListener('change', toggleVRP);

        // VRP Application Logic
        const btnAddBlock = document.getElementById('btn-add-block');
        let currentTemplateDesc = '';
        let currentTemplateProc = '';
        let currentTemplateObs = '';

        // Update template buffer on change (don't overwrite textarea yet)
        valveSelect.addEventListener('change', function () {
            const modelId = this.value;
            const model = VALVE_MODELS.find(m => m.id == modelId);

            if (model) {
                currentTemplateDesc = model.service_description || '';
                currentTemplateProc = model.procedures || '';
                currentTemplateObs = model.observations || '';
            } else {
                currentTemplateDesc = '';
                currentTemplateProc = '';
                currentTemplateObs = '';
            }
        });

        // "Add Block" Button -> Checks if model selected
        btnAddBlock.addEventListener('click', function (e) {
            if (!valveSelect.value) {
                e.preventDefault();
                e.stopPropagation();
                alert('Selecione primeiro um modelo de Válvula.');
                // Manually close if it opened via data-bs-toggle? 
                // Using bootstrap events might be cleaner, but alert is fine for now.
                // Since data-bs-toggle is on the button, it will open. We should validate before opening ideally or handle inside modal.
                // Let's rely on Token Replacement logic to warn if nothing to apply.
            }
        });

        // Token Replacement & Append Logic
        btnApplyDetails.addEventListener('click', function () {
            if (!currentTemplateDesc) {
                alert('Nenhum modelo selecionado ou modelo sem descrição.');
                return;
            }

            const tokens = document.querySelectorAll('.vrp-token');
            let desc = currentTemplateDesc;
            let proc = currentTemplateProc;
            let obs = currentTemplateObs;

            // Replace in Desc
            tokens.forEach(input => {
                const token = input.dataset.token;
                const value = input.value;
                if (value) {
                    desc = desc.replaceAll(token, value);
                }
            });

            // Prepend Model Name for clarity and searchability
            const modelName = valveSelect.options[valveSelect.selectedIndex].text;
            if (modelName && !desc.includes(modelName)) { // Avoid doubling if template already has it
                desc = "Referente a: " + modelName + "\n\n" + desc;
            }

            // Append to Textareas
            const separator = servicoInput.value ? "\n\n" : "";
            servicoInput.value += separator + desc;

            // Prevent Duplicate Procedures
            if (proc && !procedInput.value.includes(proc)) {
                const procSep = procedInput.value ? "\n\n" : "";
                procedInput.value += procSep + proc;
            }

            // Prevent Duplicate Observations
            if (obs && !obsInput.value.includes(obs)) {
                const obsSep = obsInput.value ? "\n\n" : "";
                obsInput.value += obsSep + obs;
            }

            updatePreview();

            // Clear Modal Inputs? Optional.
            tokens.forEach(input => input.value = '');

            // Close modal
            const modalEl = document.getElementById('modalVRPDetails');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        });

        // --- Optional Parts Helper ---
        const btnAddParts = document.getElementById('btn-add-parts');
        btnAddParts.addEventListener('click', function () {
            const partsText = "Obs. Caso seja necessário (vr Unitario):\n" +
                "Diafragma de 2”..........................................R$  354,00\n" +
                "Mola auxiliar de 2”..................................... R$  145,00\n" +
                "Manômetro.................................................. R$ 150,00";

            if (!obsInput.value.includes("Diafragma de 2”")) {
                const sep = obsInput.value ? "\n\n" : "";
                obsInput.value += sep + partsText;
                updatePreview();
            } else {
                alert('A lista de peças opcionais já foi adicionada.');
            }
        });

        // --- Item Logic (Simplified) ---
        const tbody = document.getElementById('itens-body');
        const btnAdd = document.getElementById('btn-add-item');
        const totalSpan = document.getElementById('total-geral');

        function addRow(data = null) {
            const tr = document.createElement('tr');
            const desc = data ? data.descricao : '';
            const qtd = data ? data.quantidade : 1;
            const val = data ? data.valor_unitario : '';

            tr.innerHTML = `
                <td><input type="text" name="descricao[]" class="form-control" placeholder="Item" required value="${desc}"></td>
                <td><input type="number" name="quantidade[]" class="form-control qtd" value="${qtd}" min="1"></td>
                <td><input type="number" name="valor_unitario[]" class="form-control val" step="0.01" value="${val}"></td>
                <td><span class="row-total fw-bold">0.00</span></td>
                <td><button type="button" class="btn btn-sm btn-danger btn-rm"><i class="fa-solid fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            attachRowEvents(tr);

            // If data loaded, trigger calc
            if (data) {
                const event = new Event('input');
                tr.querySelector('.qtd').dispatchEvent(event);
            }
        }

        function attachRowEvents(tr) {
            const qtd = tr.querySelector('.qtd');
            const val = tr.querySelector('.val');
            const btnRm = tr.querySelector('.btn-rm');
            const total = tr.querySelector('.row-total');

            function calc() {
                const q = parseFloat(qtd.value) || 0;
                const v = parseFloat(val.value) || 0;
                const t = q * v;
                total.textContent = t.toFixed(2);
                calcGrandTotal();
            }

            qtd.addEventListener('input', calc);
            val.addEventListener('input', calc);
            btnRm.addEventListener('click', () => { tr.remove(); calcGrandTotal(); });
        }

        function calcGrandTotal() {
            let gt = 0;
            document.querySelectorAll('.row-total').forEach(el => gt += parseFloat(el.textContent));
            totalSpan.textContent = gt.toFixed(2);
            prevTotal.textContent = gt.toFixed(2).replace('.', ',');
        }

        btnAdd.addEventListener('click', () => addRow());

        // Match Client Selection if replicating
        if (BASE_CLIENT_ID) {
            clientSelect.value = BASE_CLIENT_ID;
            // Trigger change to update preview
            const event = new Event('change');
            clientSelect.dispatchEvent(event);
        }

        // Init Items from Data
        if (typeof ITEMS_DATA !== 'undefined' && ITEMS_DATA && ITEMS_DATA.length > 0) {
            ITEMS_DATA.forEach(item => {
                const data = {
                    descricao: item.description,
                    quantidade: item.quantity,
                    valor_unitario: item.unit_price
                };
                addRow(data);
            });
        } else {
            addRow(); // Init empty row if no data
        }

        // Init
        toggleVRP();
    });
</script>