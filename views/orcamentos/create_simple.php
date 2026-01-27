<?php
// Ensure $valve_models is available
$valve_models = $valve_models ?? [];
?>
<!-- jQuery (Required for Summernote) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<!-- Summernote CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
    /* Immersive Mode Styles - Hide Dashboard Elements */
    #sidebar-wrapper,
    .dashboard-header {
        display: none !important;
    }

    #wrapper {
        padding-left: 0 !important;
    }

    #page-content-wrapper {
        width: 100% !important;
        margin: 0 !important;
        background-color: #f0f0f0;
        /* Gray desk background */
        min-height: 100vh;
        padding: 0 !important;
    }

    /* Toolbar Bar (Top Fixed) */
    .editor-toolbar-container {
        position: sticky;
        top: 0;
        z-index: 1050;
        background: #e9ecef;
        border-bottom: 1px solid #ccc;
        padding: 10px 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* A4 Page Container */
    .a4-page {
        width: 210mm;
        min-height: 297mm;
        background: white;
        margin: 30px auto;
        padding: 20mm;
        /* A4 Standard Margins */
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    /* Print Preview Look inside Editor */
    .header-section {
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        padding-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header-logo {
        max-width: 180px;
    }

    .header-info {
        font-size: 0.9em;
        text-align: right;
        color: #555;
    }

    /* Summernote Overrides to blend in */
    .note-editor.note-frame {
        border: none !important;
        box-shadow: none !important;
    }

    .note-editor .note-toolbar {
        /* Move toolbar to sticky Top Bar if possible, or style sticky here */
        position: sticky;
        top: 60px;
        /* Below our main bar */
        z-index: 1040;
        background: white;
        border-bottom: 1px solid #ddd;
    }

    .note-resizebar {
        display: none !important;
    }

    .note-editable {
        padding: 0 !important;
        /* Let A4 padding handle it */
        min-height: 200mm;
    }

    /* Floating Bottom Action for Save */
    .fab-save {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1100;
    }
</style>

<!-- Top Action Bar -->
<div class="editor-toolbar-container">
    <div class="d-flex align-items-center">
        <a href="<?= \BASE_URL ?>/orcamentos" class="btn btn-outline-secondary me-3">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
        <h5 class="mb-0 text-dark">
            <i class="fa-solid fa-file-word me-2"></i>
            <?= !empty($edit_mode) ? 'Editar Orçamento #' . $orcamento_id : 'Orçamento - Edição Direta' ?>
        </h5>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Client Selector -->
        <div style="width: 300px;">
            <select class="form-select searchable" id="client_id" name="client_id" form="editorForm" required>
                <option value="" selected disabled>-- Selecione o Cliente --</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>" <?= (isset($selected_client_id) && $selected_client_id == $cliente['id']) ? 'selected' : '' ?>
                        data-name="<?= htmlspecialchars($cliente['name']) ?>"
                        data-address="<?= htmlspecialchars($cliente['address']) ?>"
                        data-doc="<?= htmlspecialchars($cliente['cnpj'] ?: $cliente['documento']) ?>"
                        data-resp="<?= htmlspecialchars($cliente['responsavel']) ?>">
                        <?= htmlspecialchars($cliente['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Templates -->
        <div class="dropdown">
            <button class="btn btn-info dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
                <i class="fa-solid fa-copy"></i> Modelos
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <h6 class="dropdown-header">Modelos de Orçamento</h6>
                </li>
                <?php foreach ($templates as $tmpl): ?>
                    <li><a class="dropdown-item template-btn" href="#" data-id="<?= $tmpl['id'] ?>">
                            <?= $tmpl['name'] ?>
                        </a></li>
                <?php endforeach; ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="#" id="clear-editor">
                        <i class="fa-solid fa-eraser"></i> Limpar Tudo
                    </a></li>
            </ul>
        </div>

        <!-- Save Button (Duplicated here for ease) -->
        <button type="submit" form="editorForm" class="btn btn-success fw-bold">
            <i class="fa-solid fa-save me-2"></i> Salvar
        </button>
    </div>
</div>

<form action="<?= \BASE_URL ?>/orcamentos/store_simple" method="POST" id="editorForm">
    <?php if (!empty($edit_mode)): ?>
        <input type="hidden" name="id" value="<?= $orcamento_id ?>">
    <?php endif; ?>
    <!-- Extraction Inputs -->
    <input type="hidden" name="garantia" id="input_garantia">
    <input type="hidden" name="validade" id="input_validade">
    <input type="hidden" name="forma_pagamento" id="input_pagamento">
    <input type="hidden" name="observacoes" id="input_observacoes">
    <input type="hidden" name="assunto" id="input_assunto">

    <!-- The A4 Page -->
    <div class="a4-page">
        <!-- Visual Header (Read-Only) -->
        <div class="header-section">
            <img src="<?= \ASSET_URL ?>/assets/img/logo.jpg" alt="Logo" class="header-logo">
            <div class="header-info">
                <strong>MIK – SERVIÇOS HIDRAULICOS LTDA</strong><br>
                Av dos Imarés, 1383 – Indianópolis – SP<br>
                Tel: (11) 5579-0835 / (11) 99376-4733
            </div>
        </div>

        <!-- The Content Editor -->
        <textarea id="summernote" name="content_html"><?= $base_content ?? '' ?></textarea>

        <!-- Footer / Total (Integrated) -->
        <div class="mt-5 pt-3 border-top d-flex justify-content-end align-items-center">
            <strong class="me-3 fs-5">TOTAL R$</strong>
            <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-end"
                style="width: 200px; border: none; border-bottom: 2px solid #000; background: transparent;" name="total"
                id="total_field" value="<?= $base_total ?? '0.00' ?>" placeholder="0.00">
        </div>
    </div>
</form>

<script>
    const BUDGET_TEMPLATES = <?= json_encode($templates) ?>;
</script>

<script>
    $(document).ready(function () {
        // Init Summernote
        $('#summernote').summernote({
            placeholder: 'Selecione um cliente para iniciar ou comece a digitar...',
            tabsize: 2,
            lang: 'pt-BR',
            focus: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear', 'fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ],
            callbacks: {
                onInit: function () {
                    // Remove default border to blend with A4 page
                    $('.note-editor').addClass('border-0');
                    $('.note-statusbar').hide();
                }
            }
        });

        // Smart Ectraction Logic on Submit
        $('#editorForm').on('submit', function () {
            // Get HTML
            let code = $('#summernote').summernote('code');
            let $dom = $('<div>').html(code);

            // Extract Fields by ID
            let garantia = $dom.find('#extract-garantia').text().trim();
            let validade = $dom.find('#extract-validade').text().trim();
            let pagamento = $dom.find('#extract-pagamento').text().trim();
            let observacoes = $dom.find('#extract-observacoes').html(); // Keep HTML for obs

            // Extract Assunto
            let assunto = $dom.find('#extract-assunto').text().replace('Assunto:', '').trim();
            if (!assunto) {
                // Fallback: try to find "Assunto:" in any H4 or strong tag
                let fullText = $dom.text();
                let match = fullText.match(/Assunto:\s*(.*?)(\n|$)/);
                if (match) assunto = match[1].trim();
            }

            // Make sure not to capture placeholder text if extraction fails or isn't present
            // Fallback: If not found, leave empty or try regex? 
            // For now, rely on ID presence.

            $('#input_garantia').val(garantia);
            $('#input_validade').val(validade);
            $('#input_pagamento').val(pagamento);
            $('#input_observacoes').val(observacoes);
            $('#input_assunto').val(assunto);

            // Also ensure Total is clean
            // Total is already an input field outside summernote, handled by browser form submit
        });

        // Client Selection Logic
        $('#client_id').change(function () {
            const opt = $(this).find(':selected');
            const name = opt.data('name');
            const address = opt.data('address');
            const doc = opt.data('doc');
            const resp = opt.data('resp') || 'Responsável';
            const today = new Date().toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' });

            // Create the Header Block
            const newHeaderHtml = `
            <div id="doc-client-header" style="font-family: inherit; color: #000; margin-bottom: 20px;">
                <div style="text-align: right; margin-bottom: 20px;">
                    São Paulo, ${today}.
                </div>
                <div style="margin-bottom: 20px;">
                    <strong>${name}</strong><br>
                    ${address}<br>
                    <strong>CNPJ/CPF: ${doc}</strong>
                </div>
                <div style="margin-bottom: 10px;">
                    A/C Sr(a). ${resp}
                </div>
                <hr>
            </div>
            `;

            // Get current content
            let code = $('#summernote').summernote('code');

            // Temporary DOM to parse
            let $dom = $('<div>').html(code);

            if ($dom.find('#doc-client-header').length > 0) {
                // Replace existing header
                $dom.find('#doc-client-header').replaceWith(newHeaderHtml);
            } else {
                // Prepend new header
                $dom.prepend(newHeaderHtml);
            }

            // Set code back
            $('#summernote').summernote('code', $dom.html());
        });

        // Template Loading
        $('.template-btn').click(function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const tmpl = BUDGET_TEMPLATES.find(t => t.id == id);

            if (tmpl) {
                if (!confirm('Isso irá substituir o corpo do texto atual. O cabeçalho do cliente será mantido. Continuar?')) return;

                // 1. Capture current header if exists
                let currentCode = $('#summernote').summernote('code');
                let $dom = $('<div>').html(currentCode);
                let headerHtml = '';

                if ($dom.find('#doc-client-header').length > 0) {
                    headerHtml = $('<div>').append($dom.find('#doc-client-header').clone()).html();
                }

                // 2. Build new body content (GENERIC NOW)
                let newBodyHtml = `
                <h4 id="extract-assunto">Assunto: ${tmpl.name}</h4>
                <br>
                <div class="content-block">
                    Prezado Senhor;<br>
                    Conforme solicitação de VS temos a satisfação em colocar a disposição os nossos conhecimentos técnicos, para a execução dos serviços abaixo.
                </div>
                <br>
                <p><strong>Descrição do Serviço:</strong><br>${nl2br(tmpl.service_description)}</p>
                <br>
                <p><strong>Procedimentos:</strong><br>${nl2br(tmpl.procedures)}</p>
                <br>
                <p><strong>Duração da Obra: (Estimativa)</strong><br>
                01 dia trabalhado.</p>
                <br>
                ${tmpl.observations ? `<p><strong>Observações:</strong><br><span id="extract-observacoes">${nl2br(tmpl.observations)}</span></p><br>` : ''}
                
                <hr>
                
                <p><strong>Custo sem ART: Validade da Proposta <span id="extract-validade">10 dias</span></strong></p>
                <p><strong>Valor:</strong> A Combinar</p>
                <br>
                
                <div style="background-color: #FFFF00; padding: 5px;">
                    <span id="extract-garantia">Garantia de 06 (seis) meses, exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas. Conforme NBR 5626 , a verificação do funcionamento das válvulas redutoras de pressão deve ser semestral.</span>
                </div>
                <br>

                <p><strong>Forma de Pagamento: (sugestão)</strong><br>
                <span id="extract-pagamento">VRP - fatura R$ 4.300,00 - 28 dias da GyB para o Condominio</span>
                </p>
                <br>

                <p><strong>Considerações:</strong></p>
                <ul>
                    <li>É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.</li>
                    <li>É de responsabilidade de a contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção, designar um funcionário de manutenção do prédio para, um local para guarda de ferramentas e material, bem com um vestiário e sanitários para uso do nosso pessoal.</li>
                    <li>O prazo para a execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.</li>
                    <li>Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.</li>
                </ul>
                <div style="background-color: #FFFF00; padding: 5px;">
                    Todos os serviços serão executados:<br>
                    Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.<br>
                    Empresa possuidora de seguro de sinistro de obra.<br>
                    Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.
                </div>
                <ul>
                    <li>Workshop nas fabricas.</li>
                    <li>Cumprindo com as exigências da NBR 5626 com garantia de 06 (seis) meses, exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas.</li>
                    <li>Empresa desde 1982.</li>
                </ul>
                <br>
                <p>Sem mais para o momento ficamos a sua disposição para quaisquer outras dúvidas e esclarecimento que se fizer necessário.</p>
                <br>
                <p>Atenciosamente,<br>
                <strong>MIK SERVIÇOS HIDRÁULICOS EIRELI-ME.</strong><br>
                Mauro Imoto.</p>
                `;

                // 3. Combine
                let finalHtml = headerHtml + newBodyHtml;

                // 4. Set code
                $('#summernote').summernote('code', finalHtml);
            }
        });

        $('#clear-editor').click(function (e) {
            e.preventDefault();
            if (confirm('Limpar tudo?')) {
                $('#summernote').summernote('code', '');
            }
        });

        function nl2br(str) {
            return str ? str.replace(/\n/g, '<br>') : '';
        }
    });
</script>