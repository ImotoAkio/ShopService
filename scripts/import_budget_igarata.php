<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // ==========================================
    // 1. Create Model (Template)
    // ==========================================
    $modelName = "Manutenção e Substituição de VRP (Varb 100)";

    $serviceDescription = "- Manutenção preventiva de 02 (duas) VRPs 100 e filtros em Y de 2”, para abastecer AF (agua Fria) instaladas em Quadro de Estação de Válvula Redutora duplo em cobre no teto da garagem de um prédio de 17 andares para atender até 5º andar; com PDS (pressão dinâmica de saída) em 4 kgf/cm2.
- Manutenção preventiva de 02 (duas) VRPs 100 e filtros em Y de 2”, para abastecer AQ (agua quente), instaladas em Quadro de Estação de Válvula Redutora duplo em cobre no teto da garagem de um prédio de 17 andares para atender até 5º andar; com PDS (pressão dinâmica de saída) em 4 kgf/cm2.
- Substituir 02 (duas) VRPs 100 e manutenção de filtros em Y de 21/2” para abastecer descargas devido corpo inferior furada por oxidação quente), instaladas em Quadro de Estação de Válvula Redutora duplo em cobre no teto da garagem de um prédio de 17º andares para atender até 5 andar; com PDS (pressão dinâmica de saída) em 4 kgf/cm2.";

    $procedures = "Manutenção:
1- A ser executada no próprio local;
2- Isolamento de uma linha da Estação por vez.
3- Fechamento do registro de entrada e saída da linha da Válvula Redutora, uma por vez.
(Não nos responsabilizamos por avarias dos registros, que podem estar funcionando plenamente, porem na abertura/fechamento podem ocorrer a avaria por oxidação de estarem muito tempo sem movimento ou até mesmo desgaste da peça.) 
4- Desmontagem geral de uma VRP e Filtro por vez.
5- Remoção das incrustações de ferrugens internas dos corpos da válvula e filtros, por fricção e escovamento.
6- Remontagem, teste estanquiedade e regulagem da pressão de saída em até 4 kg/cm2, conforme recomendação da NBR 5626 da ABNT;
7- No aspecto externo as VRPs estão aparentando boas condições; porem após a abertura do corpo da VRP para a manutenção poderá deparar com a necessidade da troca de mais algumas peças internas; ou ate mesmo a sua substituição.
8- Como as VRPs (válvula redutora de pressão) estão instaladas em quadros duplos o prédio não devera ficar desabastecido durante o serviço.
9- Devido existência de perfis metálicos apoiando a base da VRP, para sua manutenção sera necessário a sua remoção.

Substituição:
- Isolar a alimentação da linha fechando os registros da linha individualmente.
- Remover o suporte metálico.
- Seccionar entre os dois conectores da entrada da linha e soltar a união e remover a VRP.
- Remontar aplicando VRP 100 da Varb de 21/2”.
- Substituir os adaptadores das entradas em PVC Marrom por conectores de cobre
- Regular a PDS (pressão dinâmica de saída) em até 4 kgf/cm2 conforme NBR 566 da ABNT.";

    $observations = "A deficiência no funcionamento ou obstrução da VRP pode ocasionar:
- Fuga de água. (Quando do banho a água esquenta ou esfria de mais, não conseguindo regular a temperatura da água).
- Diminuição de água nas torneiras em alguns horários.
- Impossibilidade de usar dois chuveiros simultâneos.
- Falta de água em horários de picos de consumo.
- Necessidade de aumentar a PDS (pressão dinâmica de saída) acima da regulamentar para compensar a vazão.

A pressão de saída acima da regulamentar pode ocasionar:
Provável rompimento de flexíveis, conexões de filtros de água, engate de maquinas de lavar roupa e louças; 
Golpe de aliete e ruídos na rede; 
Perda de água pela caixa aclopada aumentando a conta de água do condomínio. Desconforto na utilização das torneiras devido jato forte etc.
Aumento no consumo de água.

Considerações:
Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada.
Horário de trabalho em dias úteis, segunda á sexta das 09:00 ás 17:00 h com 1 h de almoço.
É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundos de vícios oculto.
É de responsabilidade da contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção.  
O prazo para a execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.
Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.
Todos os serviços serão executados: 
Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.
Empresa possuidora de seguro de sinistro de obra.
Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.
Workshop nas fabricas.
Cumprindo com as exigências da NBR 5626 com garantia de 06 (seis) meses, exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas. 
Empresa desde 1982.";

    // Insert Model
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
    $clientName = "Condomínio Igaratá";
    $stmtClient = $db->prepare("SELECT id FROM clientes WHERE name LIKE ?");
    $stmtClient->execute(['%' . $clientName . '%']);
    $client = $stmtClient->fetch();

    if ($client) {
        $clientId = $client['id'];
        echo "Client found: $clientId\n";
    } else {
        $stmtInsertClient = $db->prepare("INSERT INTO clientes (
            name, address, responsavel, zelador_nome, zelador_tel, zelador_email, 
            sindico_nome, sindico_tel, email, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmtInsertClient->execute([
            $clientName,
            "Rua Duarte de Carvalho, 155, Tatuapé - São Paulo - SP",
            "Sr. Waldir",
            "Sr. Adriano",
            "(11) 95920-1818",
            "edifício.igarata@gmail.com",
            "Sr. Waldir",
            "99324 8121",
            "edifício.igarata@gmail.com"
        ]);
        $clientId = $db->lastInsertId();
        echo "Client created: $clientId\n";
    }

    // Budget Record
    $total = 7900.00; // Total Mik
    $assunto = "Manutenção de VRP (Válvula Redutora de Pressão) 100 da Varb.";
    $duracao = "02 dias trabalhados.";
    $garantia = "06 (seis) meses";
    $pagamento = "VRPs novas fatura direto do fabricante para o Condomínio. Após o termino do serviço 01 (uma) parcela de R$ 1.975,00 e mais 03 (duas) parcelas de R$ 1.975,00 para 30 dias com nota fiscal e boleto bancário.";
    $validade = "10 dias";

    // Notes for budget record might include specific parts costs mentioned
    $obsBudget = $observations . "\n\nCUSTOS ADICIONAIS:\nVRP Varb 21/2: 2 x R$ 4.441,00\nOU VRP GyB 21/2: 2 x R$ 4.300,00\nTognini R$ 2,780,74 + 1 uniao 21/2\n\nPeças Avulsas (se necessário): Diafragma 2” R$ 354,00; Mola 2” R$ 145,00; Manômetro R$ 150,00";

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
        2, // Marcia Imoto
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
        ['desc' => 'M.O manutenção 04 VRPs e substituição 2 VRPs', 'qtd' => 1, 'val' => 3000.00, 'total' => 3000.00],
        ['desc' => 'Material', 'qtd' => 1, 'val' => 4900.00, 'total' => 4900.00]
    ];

    $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$orcId, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
    }
    echo "Items inserted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
