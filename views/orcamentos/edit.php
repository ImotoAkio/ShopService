<?php
// Ensure $valve_models is available
$valve_models = $valve_models ?? [];
$itens_json = json_encode($itens ?? []);
?>
<link rel="stylesheet" href="<?= \ASSET_URL ?>/assets/css/accessibility.css">

<div class="row h-100">
    <!-- Left Column: Form -->
    <div class="col-lg-7 col-md-12 overflow-auto" style="height: calc(100vh - 100px);">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-pen-to-square me-2"></i> Editar Orçamento #
                    <?= $orcamento['id'] ?>
                </h5>
                <span class="badge bg-light text-dark">Data:
                    <?= date('d/m/Y', strtotime($orcamento['created_at'])) ?>
                </span>
            </div>
            <div class="card-body">
                <form action="<?= \BASE_URL ?>/orcamentos/update" method="POST" id="orcamentoForm">
                    <input type="hidden" name="id" value="<?= $orcamento['id'] ?>">

                    <!-- Cliente -->
                    <div class="mb-4">
                        <label for="client_id" class="form-label">Cliente</label>
                        <select class="form-select form-select-lg" id="client_id" name="client_id" required>
                            <option value="" disabled>Selecione um cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" <?= $cliente['id'] == $orcamento['client_id'] ? 'selected' : '' ?>
                                    data-name="
                                <?= htmlspecialchars($cliente['name']) ?>"
                                    data-address="
                                <?= htmlspecialchars($cliente['address']) ?>"
                                    data-doc="
                                <?= htmlspecialchars($cliente['documento']) ?>"
                                    data-cnpj="
                                <?= htmlspecialchars($cliente['cnpj']) ?>">
                                    <?= htmlspecialchars($cliente['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- OS Link -->
                    <div class="mb-3">
                        <label for="os_id" class="form-label">Vincular Ordem de Serviço (Opcional)</label>
                        <select class="form-select" id="os_id" name="os_id">
                            <option value="">-- Sem Vínculo --</option>
                            <?php foreach ($oss as $os): ?>
                                <option value="<?= $os['id'] ?>" <?= $os['id'] == $orcamento['os_id'] ? 'selected' : '' ?>
                                    data-client-id="
                                <?= $os['client_id'] ?>">
                                    OS #
                                    <?= $os['id'] ?> -
                                    <?= htmlspecialchars($os['client_name']) ?>
                                    [
                                    <?= $os['status'] ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Detalhes do Orçamento -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white fw-bold">Detalhes da Proposta</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="assunto" class="form-label">Assunto</label>
                                <input type="text" class="form-control" id="assunto" name="assunto"
                                    value="<?= htmlspecialchars($orcamento['assunto']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="servico_descricao" class="form-label">Descrição Geral do Serviço</label>
                                <textarea class="form-control" id="servico_descricao" name="servico_descricao"
                                    rows="3"><?= htmlspecialchars($orcamento['servico_descricao']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="procedimentos" class="form-label">Procedimentos</label>
                                <textarea class="form-control" id="procedimentos" name="procedimentos"
                                    rows="8"><?= htmlspecialchars($orcamento['procedimentos']) ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="duracao" class="form-label">Duração</label>
                                    <input type="text" class="form-control" id="duracao" name="duracao"
                                        value="<?= htmlspecialchars($orcamento['duracao']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="validade" class="form-label">Validade</label>
                                    <input type="text" class="form-control" id="validade" name="validade"
                                        value="<?= htmlspecialchars($orcamento['validade']) ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="garantia" class="form-label">Garantia</label>
                                    <select class="form-select" id="garantia" name="garantia">
                                        <option value="03 (três) meses" <?= ($orcamento['garantia'] == '03 (três) meses') ? 'selected' : '' ?>>03 (três) meses</option>
                                        <option value="06 (seis) meses" <?= ($orcamento['garantia'] == '06 (seis) meses' || empty($orcamento['garantia'])) ? 'selected' : '' ?>>06 (seis) meses</option>
                                        <option value="12 (doze) meses" <?= ($orcamento['garantia'] == '12 (doze) meses') ? 'selected' : '' ?>>12 (doze) meses</option>
                                        <option value="24 (vinte e quatro) meses" <?= ($orcamento['garantia'] == '24 (vinte e quatro) meses') ? 'selected' : '' ?>>24 (vinte e quatro) meses</option>
                                        <!-- Fallback for custom legacy values -->
                                        <?php if (!in_array($orcamento['garantia'], ['03 (três) meses', '06 (seis) meses', '12 (doze) meses', '24 (vinte e quatro) meses', ''])): ?>
                                            <option value="<?= htmlspecialchars($orcamento['garantia']) ?>" selected><?= htmlspecialchars($orcamento['garantia']) ?></option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="forma_pagamento" class="form-label">Pagamento</label>
                                    <input type="text" class="form-control" id="forma_pagamento" name="forma_pagamento"
                                        value="<?= htmlspecialchars($orcamento['forma_pagamento']) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes"
                                    rows="5"><?= htmlspecialchars($orcamento['observacoes']) ?></textarea>
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
                            <i class="fa-solid fa-save me-2"></i> Atualizar Orçamento
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
                    Orç.
                    <?= $orcamento['id'] ?>/
                    <?= date('Y', strtotime($orcamento['created_at'])) ?><br>
                    São Paulo,
                    <?= date('d/m/Y', strtotime($orcamento['created_at'])) ?>.
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

<script>
    const ITENS_DATA = <?= $itens_json ?>;

    document.addEventListener('DOMContentLoaded', function () {
        // --- Elements ---
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

        // --- Item Logic ---
        const tbody = document.getElementById('itens-body');
        const btnAdd = document.getElementById('btn-add-item');
        const totalSpan = document.getElementById('total-geral');

        function addRow(data = null) {
            const tr = document.createElement('tr');
            const desc = data ? data.description : '';
            const qtd = data ? data.quantity : 1;
            const val = data ? data.unit_price : '';

            tr.innerHTML = `
                <td><input type="text" name="descricao[]" class="form-control" placeholder="Item" required value="${desc}"></td>
                <td><input type="number" name="quantidade[]" class="form-control qtd" value="${qtd}" min="1"></td>
                <td><input type="number" name="valor_unitario[]" class="form-control val" step="0.01" value="${val}"></td>
                <td><span class="row-total fw-bold">0.00</span></td>
                <td><button type="button" class="btn btn-sm btn-danger btn-rm"><i class="fa-solid fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            attachRowEvents(tr);

            // Force calc for this row if data present
            const qtdInput = tr.querySelector('.qtd');
            const valInput = tr.querySelector('.val');
            // Trigger input event manually or call calc function?
            // Let's manually trigger logic
            const evt = new Event('input');
            qtdInput.dispatchEvent(evt);
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

        // Init Items from Data
        if (ITENS_DATA && ITENS_DATA.length > 0) {
            ITENS_DATA.forEach(item => addRow(item));
        } else {
            addRow();
        }

        // Init Preview
        updatePreview();
    });
</script>