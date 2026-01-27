<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Novo Orçamento (Hierárquico)</h1>
        <a href="<?= \BASE_URL ?>/orcamentos" class="btn btn-secondary">Voltar</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= \BASE_URL ?>/orcamentos/store_v2" method="POST" id="budgetForm">
        <!-- Level 1: Proposal Info -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Dados da Proposta
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Cliente *</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Assunto</label>
                        <input type="text" name="assunto" class="form-control"
                            placeholder="Ex: Revitalização do Sistema Hidráulico">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Garantia Geral</label>
                        <input type="text" name="garantia" class="form-control" value="12 meses">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Validade</label>
                        <input type="text" name="validade" class="form-control" value="15 dias">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Pagamento</label>
                        <input type="text" name="forma_pagamento" class="form-control" value="A combinar">
                    </div>
                </div>
            </div>
        </div>

        <!-- Level 2: Sectors -->
        <div id="sectors-container">
            <!-- Sectors will be added here via JS -->
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-success" onclick="addSector()">
                <i class="fas fa-plus"></i> Adicionar Setor/Bloco
            </button>
        </div>

        <!-- Grand Total -->
        <div class="card mb-4">
            <div class="card-body text-end">
                <h3>Total Geral: R$ <span id="grand-total">0,00</span></h3>
                <input type="hidden" name="total_orcamento" id="input-grand-total" value="0">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Salvar Orçamento Completo</button>
    </form>
</div>

<!-- Templates for JS -->
<template id="tpl-sector">
    <div class="card mb-4 sector-card border-primary" data-sector-index="{sIndex}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Setor: <input type="text" name="sectors[{sIndex}][name]"
                    class="form-control d-inline-block w-50" placeholder="Ex: Torre A" required></h5>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeElement(this, '.sector-card')">Remover
                Setor</button>
        </div>
        <div class="card-body">
            <div class="zones-container" id="zones-container-{sIndex}"></div>
            <button type="button" class="btn btn-info btn-sm mt-2 text-white" onclick="addZone({sIndex})">
                <i class="fas fa-plus"></i> Adicionar Zona de Pressão
            </button>
        </div>
    </div>
</template>

<template id="tpl-zone">
    <div class="card mb-3 zone-card border-info ms-3" data-zone-index="{zIndex}">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="d-flex flex-wrap gap-2 w-100 align-items-center">
                <strong>Zona:</strong>
                <input type="text" name="sectors[{sIndex}][zones][{zIndex}][name]"
                    class="form-control form-control-sm w-auto" placeholder="Nome (Ex: Zona Baixa)" required>

                <select name="sectors[{sIndex}][zones][{zIndex}][pipeline_material]"
                    class="form-select form-select-sm w-auto" required>
                    <option value="">Material...</option>
                    <option value="PVC Marrom">PVC Marrom</option>
                    <option value="PPR">PPR</option>
                    <option value="Cobre">Cobre</option>
                    <option value="Galvanizado">Galvanizado</option>
                </select>

                <input type="text" name="sectors[{sIndex}][zones][{zIndex}][pressure_value]"
                    class="form-control form-control-sm" style="width: 80px;" placeholder="Pressão">
                <select name="sectors[{sIndex}][zones][{zIndex}][pressure_unit]" class="form-select form-select-sm"
                    style="width: 100px;">
                    <option value="kgf/cm²">kgf/cm²</option>
                    <option value="mca">mca</option>
                </select>

                <input type="text" name="sectors[{sIndex}][zones][{zIndex}][floor_range]"
                    class="form-control form-control-sm w-auto" placeholder="Andares (Ex: 1 ao 5)">
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm ms-2"
                onclick="removeElement(this, '.zone-card')">X</button>
        </div>
        <div class="card-body p-2">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th width="40%">Item</th>
                        <th width="20%">Marca/Modelo</th>
                        <th width="10%">Ø</th>
                        <th width="10%">Qtd</th>
                        <th width="10%">Unit.</th>
                        <th width="10%">Total</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody class="items-tbody" id="items-tbody-{sIndex}-{zIndex}"></tbody>
            </table>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addItem({sIndex}, {zIndex})">+ Item</button>
        </div>
    </div>
</template>

<template id="tpl-item">
    <tr class="item-row">
        <td><input type="text" name="sectors[{sIndex}][zones][{zIndex}][items][{iIndex}][description]"
                class="form-control form-control-sm" placeholder="Descrição" required></td>
        <td><input type="text" name="sectors[{sIndex}][zones][{zIndex}][items][{iIndex}][brand_model]"
                class="form-control form-control-sm" placeholder="Ex: Bermad"></td>
        <td><input type="text" name="sectors[{sIndex}][zones][{zIndex}][items][{iIndex}][diameter]"
                class="form-control form-control-sm" placeholder="Ex: 2 1/2"></td>
        <td><input type="number" name="sectors[{sIndex}][zones][{zIndex}][items][{iIndex}][quantity]"
                class="form-control form-control-sm qty" value="1" min="1" onchange="calcRow(this)"></td>
        <td><input type="number" name="sectors[{sIndex}][zones][{zIndex}][items][{iIndex}][unit_price]"
                class="form-control form-control-sm unit" step="0.01" value="0.00" onchange="calcRow(this)"></td>
        <td><input type="text" name="sectors[{sIndex}][zones][{zIndex}][items][{iIndex}][total_price]"
                class="form-control form-control-sm total" readonly tabindex="-1"></td>
        <td class="text-center"><button type="button" class="btn btn-link text-danger p-0"
                onclick="removeElement(this, '.item-row')"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<script>
    let sCounter = 0;
    let zCounter = 0;
    let iCounter = 0;

    function addSector() {
        const tpl = document.getElementById('tpl-sector').innerHTML;
        const html = tpl.replace(/{sIndex}/g, sCounter++);
        document.getElementById('sectors-container').insertAdjacentHTML('beforeend', html);

        // Auto add 1 zone
        addZone(sCounter - 1);
    }

    function addZone(sIndex) {
        const tpl = document.getElementById('tpl-zone').innerHTML;
        const html = tpl.replace(/{sIndex}/g, sIndex).replace(/{zIndex}/g, zCounter++);
        document.getElementById('zones-container-' + sIndex).insertAdjacentHTML('beforeend', html);

        // Auto add 1 item
        addItem(sIndex, zCounter - 1);
    }

    function addItem(sIndex, zIndex) {
        const tpl = document.getElementById('tpl-item').innerHTML;
        const html = tpl.replace(/{sIndex}/g, sIndex).replace(/{zIndex}/g, zIndex).replace(/{iIndex}/g, iCounter++);
        document.getElementById('items-tbody-' + sIndex + '-' + zIndex).insertAdjacentHTML('beforeend', html);
    }

    function removeElement(btn, selector) {
        if (confirm('Tem certeza?')) {
            const row = btn.closest(selector);
            row.remove();
            calcGrandTotal();
        }
    }

    function calcRow(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const unit = parseFloat(row.querySelector('.unit').value) || 0;
        const total = qty * unit;
        row.querySelector('.total').value = total.toFixed(2);
        calcGrandTotal();
    }

    function calcGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.total').forEach(el => {
            grandTotal += parseFloat(el.value) || 0;
        });
        document.getElementById('grand-total').innerText = grandTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        document.getElementById('input-grand-total').value = grandTotal.toFixed(2);
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        addSector(); // Start with one sector
    });
</script>