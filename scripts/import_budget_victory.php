<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // ==========================================
    // 1. Create Model (Template)
    // ==========================================
    $modelName = "Substituição de Registro Geral 1 1/4\" (Deca)";

    // Abstracting specifics (Apt 114) for the generic template
    $serviceDescription = "Substituição de 01 (um) registro geral [LOCAL/APTO], gaveta bruto 11/4\", sem estanqueidade. Aplicar registro gaveta Deca, semi industrial, volante amarelo, 150 libras.";

    $procedures = "Procedimento Padrão para Substituição de Registro:
1- Fechamento da prumada de abastecimento do local.
(aproximadamente 02 horas sem água)
2- Remoção da peça danificada.
3- Limpeza da rosca ou adaptação da tubulação existente.
4- Instalação do novo registro com vedação adequada.
5- Reabertura da água e testes de estanqueidade.";

    $observations = "Considerações:
Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada.
O objetivo desta proposta é a manutenção das válvulas redutoras de pressão. (Nota original, mantida)
Horário de trabalho em dias úteis, segunda á sexta das 09h00min ás 17h00min h com 1 h de almoço.
É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.
É de responsabilidade de a contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção, designar um funcionário de manutenção do prédio para acompanhar a manutenção, um local para guarda de ferramentas e material, bem com um vestiário e sanitários para uso do nosso pessoal.  
O prazo para a execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.
Todos os serviços serão executados: 
Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.
Empresa possuidora de seguro de sinistro de obra.
Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.
Workshop nas fabricas.
Cumprindo com as exigências da NBR 5626 com garantia de 01 (um) ano , exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas. 
Empresa desde 1982.";

    // Check if exists
    $stmtCheck = $db->prepare("SELECT id FROM valve_models WHERE name = ?");
    $stmtCheck->execute([$modelName]);
    if ($stmtCheck->fetch()) {
        echo "Template '$modelName' already exists.\n";
    } else {
        $stmt = $db->prepare("INSERT INTO valve_models (name, service_description, procedures, observations) VALUES (?, ?, ?, ?)");
        $stmt->execute([$modelName, $serviceDescription, $procedures, $observations]);
        echo "Template '$modelName' inserted.\n";
    }

    // ==========================================
    // 2. Insert Client & Specific Budget
    // ==========================================
    $clientName = "Cond. Victory Point";

    $stmtClient = $db->prepare("SELECT id FROM clientes WHERE name LIKE ?");
    $stmtClient->execute(['%' . $clientName . '%']);
    $client = $stmtClient->fetch();

    if ($client) {
        $clientId = $client['id'];
        echo "Client found: $clientId\n";
    } else {
        // Insert Client
        // Sr. Artur (Sindico) Tel 99961-5695
        // Felipe (zelador) Tel: (11) 96020-2924
        // E-mail. edificiovictorypoint@gmail.com

        $stmtInsertClient = $db->prepare("INSERT INTO clientes (
            name, address, 
            responsavel, zelador_nome, zelador_tel, email,
            sindico_nome, sindico_tel, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmtInsertClient->execute([
            $clientName,
            "Rua Jorge Tibiriçá, 74, Vila Mariana – São Paulo - SP",
            "Sr. Artur", // Responsavel
            "Felipe",
            "(11) 96020-2924", // Zelador
            "edificiovictorypoint@gmail.com", // Email
            "Sr. Artur",
            "99961-5695" // Sindico
        ]);
        $clientId = $db->lastInsertId();
        echo "Client created: $clientId\n";
    }

    // Budget
    $assunto = "Substituição de Registro";
    $assuntoFull = "Substituição de Registro (Apt 114)";
    $duracao = "01 dia.";
    $garantia = "01 (um) ano.";
    $pagamento = "Após termino do serviço 01 (uma) parcela de R$ 550,00 com nota fiscal e boleto bancário.";
    $validade = "10 dias";
    $total = 550.00;

    // Specific Service Description for this budget
    $specService = "Substituição de 01 (um) registro geral do apartamento 114, gaveta bruto 11/4\", sem estanqueidade. Aplicar registro gaveta Deca, semi industrial, volante amarelo, 150 libras.";

    $obsBudget = $observations . "\n\nObs.: Para execução do trabalho, aproximadamente 02 horas sem água.";

    $stmtOrc = $db->prepare("INSERT INTO orcamentos (
        client_id, user_id, status, total, assunto, 
        servico_descricao, procedimentos, duracao, garantia, 
        forma_pagamento, observacoes, validade, created_at
    ) VALUES (
        ?, ?, 'Pendente', ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, NOW()
    )");

    $stmtOrc->execute([
        $clientId,
        2,
        $total,
        $assuntoFull,
        $specService,
        $procedures,
        $duracao,
        $garantia,
        $pagamento,
        $obsBudget,
        $validade
    ]);
    $orcId = $db->lastInsertId();
    echo "Budget created: $orcId\n";

    // Items
    $items = [
        ['desc' => 'Mão de Obra e material de aplicação', 'qtd' => 1, 'val' => 550.00, 'total' => 550.00]
    ];

    $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$orcId, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
    }
    echo "Items inserted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
