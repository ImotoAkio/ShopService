<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-file-import me-2"></i> Importar Orçamentos via JSON</h3>
    <a href="<?= \BASE_URL ?>/orcamentos" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fa-solid fa-code me-2"></i> Colar JSON Estruturado</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fa-solid fa-info-circle me-2"></i> <strong>Instrução:</strong>
                    Cole o JSON gerado pelo processo externo no campo abaixo. O sistema criará o cliente e os orçamentos
                    automaticamente.
                </div>

                <div class="accordion mb-3 shadow-sm" id="accordionSchema">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSchema">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSchema" aria-expanded="false" aria-controls="collapseSchema">
                                <i class="fa-solid fa-code me-2"></i> Ver Estrutura JSON Esperada
                            </button>
                        </h2>
                        <div id="collapseSchema" class="accordion-collapse collapse" aria-labelledby="headingSchema"
                            data-bs-parent="#accordionSchema">
                            <div class="accordion-body bg-light">
                                <pre class="mb-0 small user-select-all" style="max-height: 300px;">{
  "cliente": {
    "name": "Nome do Cliente",
    "documento": "CPF (opcional)",
    "cnpj": "CNPJ (opcional)",
    "email": "email@exemplo.com",
    "phone": "Telefone",
    "address": "Endereço Completo",
    "responsavel": "Nome do Responsável"
  },
  "orcamentos": [
    {
      "assunto": "Título do Orçamento",
      "status": "Pendente",
      "total": 1000.00,
      "data_orcamento": "YYYY-MM-DD",
      "servico_descricao": "Descrição detalhada...",
      "garantia": "Garantia (ex: 1 ano)",
      "validade": "Validade (ex: 10 dias)",
      "forma_pagamento": "Condições de Pagamento",
      "procedimentos": "Procedimentos técnicos (opcional)",
      "observacoes": "Observações adicionais...",
      "itens": [
        {
          "description": "Nome do Item",
          "quantity": 1,
          "unit_price": 100.00,
          "total_price": 100.00
        }
      ]
    }
  ]
}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="json-input" class="form-label">Conteúdo JSON:</label>
                    <textarea id="json-input" class="form-control font-monospace" rows="15" placeholder='Cole seu JSON aqui...'></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button id="btn-process" class="btn btn-success btn-lg">
                        <i class="fa-solid fa-play me-2"></i> Processar Importação
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" id="result-card" style="display: none;">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0"><i class="fa-solid fa-terminal me-2"></i> Resultado</h5>
            </div>
            <div class="card-body">
                <div id="result-message"></div>
                <pre id="result-details" class="bg-light p-3 border rounded mt-3" style="display: none;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnProcess = document.getElementById('btn-process');
        const jsonInput = document.getElementById('json-input');
        const resultCard = document.getElementById('result-card');
        const resultMessage = document.getElementById('result-message');
        const resultDetails = document.getElementById('result-details');

        btnProcess.addEventListener('click', async () => {
            const jsonText = jsonInput.value.trim();

            if (!jsonText) {
                alert('Por favor, cole o JSON no campo indicado.');
                return;
            }

            try {
                // Validate JSON syntax locally first
                const jsonObj = JSON.parse(jsonText);
            } catch (e) {
                alert('Erro de sintaxe no JSON: ' + e.message);
                return;
            }

            // Lock UI
            btnProcess.disabled = true;
            btnProcess.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Processando...';
            resultCard.style.display = 'none';

            try {
                const response = await fetch('<?= \BASE_URL ?>/orcamentos/import/process', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: jsonText // Send raw text/json
                });

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    throw new Error("Resposta inválida do servidor: " + text.substring(0, 100));
                }

                const data = await response.json();

                resultCard.style.display = 'block';
                if (data.success) {
                    resultMessage.innerHTML = `<div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i> ${data.message}</div>`;
                    if (data.data) {
                        resultDetails.style.display = 'block';
                        resultDetails.innerText = JSON.stringify(data.data, null, 2);
                    } else {
                        resultDetails.style.display = 'none';
                    }
                    // Clear input on success
                    // jsonInput.value = ''; 
                } else {
                    resultMessage.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-exclamation-triangle me-2"></i> ${data.message}</div>`;
                    resultDetails.style.display = 'none';
                }

            } catch (error) {
                resultCard.style.display = 'block';
                resultMessage.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-bug me-2"></i> Erro na requisição: ${error.message}</div>`;
            } finally {
                btnProcess.disabled = false;
                btnProcess.innerHTML = '<i class="fa-solid fa-play me-2"></i> Processar Importação';
            }
        });
    });
</script>