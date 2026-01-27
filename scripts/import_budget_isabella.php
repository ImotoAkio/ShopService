<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // ==========================================
    // 1. Create Model (Template)
    // ==========================================
    $modelName = "Manutenção VRP 25M Varb (1 1/2\")";

    $serviceDescription = "Manutenção de 02 (duas) VRPs 25M de 11/2” instaladas no SS3 no prédio de 11 andares para atender do SS1 ao 5º andar com a PDS (pressão dinâmica de saída) em 4,5 kgf. /cm2.";

    $procedures = "Fechamento do registro de entrada e saída da linha da Válvula Redutora, uma por vez.
(Não nos responsabilizamos por avarias dos registros, que podem estar funcionando plenamente, porem na abertura/fechamento podem ocorrer a avaria por oxidação de estarem muito tempo sem movimento ou até mesmo desgaste da peça.) 

1- A ser executado no próprio local.
2- Desmontagem geral do corpo da VRP;
3- Verificar o estado das peças internas e moveis e a parte interna do corpo inferior.
4- Remoção das incrustações de ferrugens internas de corpos da válvula;
5- Remontar, testar a estanqueidade e regular a pressão de saída para atender as NBR 5626 
6- Manter as duas linhas das VRPs abertas.
7- Mesmo que no aspecto externo das VRPs estarem aparentando boas condições, somente, após a abertura das VRPs poderemos constatar a necessidade da substituição de algumas peças internas ou até a troca da VRP por completo.
Esses problemas só poderão ser constatados efetivamente durante a regulagem e a desmontagem das VRPs
8- Como na estação possui 02 (duas) VRPs o prédio não deverá ficar desabastecido durante a manutenção.
9- Manter as 02(duas) VRPs em funcionamento.
-A execução da manutenção correta e periódica além de aumentar a segurança do sistema hidráulico do prédio minimiza eventuais ocorrências conforme descrito abaixo.";

    $observations = "A obstrução, pressão baixa ou mau funcionamento da VRP pode ocasionar:
- Fuga de água. (Quando do banho a água esquenta ou esfria de mais, não conseguindo regular a temperatura da água).
- Diminuição de água nas torneiras em alguns horários.
- Impossibilidade de usar dois chuveiros simultâneos.
- Falta de água em alguns horários.
- Necessidade de aumentar a PDS (pressão dinâmica de saída) acima da regulamentar.

A pressão de saída acima da pressão regulamentar pode ocasionar:
- Provável rompimento de flexíveis, conexões de PVC marrom, engate de maquinas de lavar roupa, filtros e louças; 
- Golpe de aríete e ruídos na rede; 
- Perda de água pela caixa acoplada. 
- Desconforto na utilização das torneiras devido jato forte etc.
- Aumento no consumo de água.

Considerações:
Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada.
Horário de trabalho em dias úteis, segunda á sexta das 09h00min ás 17h00min h com 1 h de almoço.
É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.
O prazo para a execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.
Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.
Todos os serviços serão executados: 
Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.
Empresa possuidora de seguro de sinistro de obra.
Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.
Workshop nas fabricas.
Cumprindo com as exigências da NBR 5626 com garantia de 06 (seis) meses, exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas. 
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
    $clientName = "Cond. Isabella Plaza";
    $cnpj = "05.347.195/0001-64";

    $stmtClient = $db->prepare("SELECT id FROM clientes WHERE name LIKE ? OR cnpj = ?");
    $stmtClient->execute(['%' . $clientName . '%', $cnpj]);
    $client = $stmtClient->fetch();

    if ($client) {
        $clientId = $client['id'];
        echo "Client found: $clientId\n";
    } else {
        // Insert Client
        // Sr. Antônio (Gerente) Tel. (11) 97187-2895 E-mail isabellaplaza@hersil.com.br

        $stmtInsertClient = $db->prepare("INSERT INTO clientes (
            name, address, cnpj,
            responsavel, cargo, phone, email,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmtInsertClient->execute([
            $clientName,
            "Av. Presidente Juscelino Kubitscheck, 28, Itaim Bibi – São Paulo - SP",
            $cnpj,
            "Sr. Antônio",
            "Gerente",
            "(11) 97187-2895",
            "isabellaplaza@hersil.com.br"
        ]);
        $clientId = $db->lastInsertId();
        echo "Client created: $clientId\n";
    }

    // Budget
    $assunto = "Manutenção de VRP (Válvula Redutora de Pressão) 25M da Varb";
    $duracao = "01 dia trabalhado.";
    $garantia = "06 (seis) meses.";
    $pagamento = "Após o termino das atividades 01 (uma) parcela de R$ 1.200,00 com nota fiscal e boleto bancário.";
    $validade = "10 dias";
    $total = 1200.00;

    $obsBudget = $observations . "\n\nCUSTOS ADICIONAIS NECESSÁRIOS (SE HOUVER):\nKit reparo 25M 11/2” ... R$ 520,00\nManômetro ... R$ 150,00";

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
        $assunto,
        $serviceDescription,
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
        ['desc' => 'Mão de obra para manutenção das 02 (duas) VRPs', 'qtd' => 1, 'val' => 1200.00, 'total' => 1200.00]
    ];

    $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$orcId, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
    }
    echo "Items inserted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
