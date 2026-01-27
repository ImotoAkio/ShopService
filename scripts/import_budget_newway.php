<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // ==========================================
    // 1. Create Model (Template)
    // ==========================================
    $modelName = "Manutenção de VRPs 405 (Água Fria - Blocos)";

    $serviceDescription = "Bloco 1 e 2.
Manutenção de 02 (duas) VRPs 405 de 21/2”, instaladas no SS1 em Quadro de Estação Redutora duplo, em PVC Marrom 21/2” do prédio de 24 andares para atender AF (água fria) até o 4º andar, com PDS (pressão dinâmica de saída) em 4 kgf. /cm2 e 01 Válvula de Alívio 430 de 21/2” com PDS (pressão dinâmica de saída) em 4,5 kgf. /cm2. 

Manutenção de 02 (duas) VRPs 405 de 21/2”, instaladas no SS1 em Quadro de Estação Redutora duplo, em PPR  21/2” do prédio de 24 andares para atender AF (água fria) do 5º até o 12º andar, com PDS (pressão dinâmica de saída) em 5,5 kgf./cm2 e 01 Válvula de Alívio 430 de 21/2” com PDS (pressão dinâmica de saída) em 6,0 kgf. /cm2. 

Bloco 3 e 4.
Manutenção de 02 (duas) VRP 405 de 21/2”, instaladas no SS1 em Quadro de Estação Redutora duplo, em PVC Marrom 21/2” de um prédio de 24 andares para atender AF (água fria) até o 4º andar, com PDS (pressão dinâmica de saída) em 4,0 kgf. /cm2 e 01 Válvula de Alívio 430 de 21/2” com PDS (pressão dinâmica de saída) em 4,5 kgf. /cm2. 

Manutenção  02 (duas) VRPs 405 de 21/2”, instaladas no SS1 em Quadro de Estação Redutora duplo, em PPR  21/2” de um prédio de 24 andares para atender AF (água fria) do 5º até o 14º andar, com PDS (pressão dinâmica de saída) em 5,5 kgf./cm2 e 01 Válvula de Alívio 430 de 21/2” com  PDS (pressão dinâmica de saída) em 6,0 kgf./cm2.";

    $procedures = "Fechamento do registro de entrada e saída da linha da Válvula Redutora, uma por vez.
(não nos responsabilizamos por avarias dos registros, que podem estar funcionando plenamente, porem na abertura/fechamento podem ocorrer a avaria por oxidação de estarem muito tempo sem movimento ou até mesmo desgaste da peça.) 
1- Manutenção corretiva e/ou preventiva de 12 VRPs (Válvulas Redutora de Pressão) de 21/2”; 04 (quatro) Válvulas de alivio 405 de 2”, a serem executadas no próprio local.
2- Desmontagem geral do corpo da válvula de controle, filtro das mangueiras, da válvula piloto; das válvulas agulha e mangueiras.
3- Limpeza interna do corpo das válvulas de controle; piloto e filtro e verificar o fluxo das mangueiras e das válvulas agulhas.
5- Manter todas as válvulas agulhas com ¼ abertas.
7- Mesmo que os aspectos externos das VRPs estarem aparentando boas condições, porém devido; o tempo sem manutenção e os problemas apresentados. Após a abertura do corpo da VRP poderemos constatar a necessidade da substituição dos diafragmas ou ate mesmo a necessidade da sua substituição por completo. 
Esses problemas só poderão ser constatados efetivamente após a abertura e quando da compatibilização da pressão com a vazão. 
8- Aferir todos os manômetros e substituir se necessário.";

    $observations = "Considerações:
Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada;
Horário de trabalho em dias úteis, segunda á Sexta das 09h00min ás 17h00min h com 1 h de almoço.
A pressão acima de 4kgf./cm2 exigidas pelas Normas 5626 pode ocasionar: provável rompimento de flexíveis, conexões de filtros de água, engate de maquinas de lavar roupa e louças; ou da própria tubulação de PVC Marrom, golpe de aliete e ruídos na rede; desconforto na utilização das torneiras devido jato forte e  perda de água pela caixa acoplada e aumento na conta de água etc.
Com pressão dentro das normas de ate 4kgf/cm2 reduz o consumo de água e chamadas de manutenção e aumenta a segurança do sistema hidráulico do prédio. 
Após manutenção recomendamos verificar os registros gerais das unidades atendidas pelas VRPs e mante-las totalmente abertas. 
Como os filtros em Y estão instaladas antes das VRPs, quando da sua manutenção será necessário desabastecer parcialmente o prédio.
É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.
É de responsabilidade de a contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção, designar um local para guarda de ferramentas e material, bem com um vestiário e sanitários para uso do nosso pessoal.  
O prazo para execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.
Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.
Como o quadro é duplo salvo juízo melhor o prédio não deverá ficar desabastecido durante o serviço.
Todos os serviços serão executados: 
Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o 
prédio de uma eventual ação trabalhista.
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
    $clientName = "Condomínio New Way";
    $stmtClient = $db->prepare("SELECT id FROM clientes WHERE name LIKE ?");
    $stmtClient->execute(['%' . $clientName . '%']);
    $client = $stmtClient->fetch();

    if ($client) {
        $clientId = $client['id'];
        echo "Client found: $clientId\n";
    } else {
        // Insert Client
        // Sra Vanessa (síndica) Tel: (11) 91225-6813 E-mail nwsindico@gmail.com
        // Sr. Adriano (adm) Tel: (11) 3208-4031 E-mail: admnw301@gmail.com

        $stmtInsertClient = $db->prepare("INSERT INTO clientes (
            name, address, responsavel, email, phone,
            sindico_nome, sindico_tel, sindico_email,
            admin_nome, admin_tel, admin_email,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmtInsertClient->execute([
            $clientName,
            "Rua Glicério, 301, Liberdade – São Paulo- SP. CEP 01514-000",
            "Sra Vanessa",
            "nwsindico@gmail.com", // Main email
            "(11) 91225-6813", // Main phone (Sindica)
            "Sra Vanessa",
            "(11) 91225-6813",
            "nwsindico@gmail.com",
            "Sr. Adriano",
            "(11) 3208-4031",
            "admnw301@gmail.com"
        ]);
        $clientId = $db->lastInsertId();
        echo "Client created: $clientId\n";
    }

    // Budget
    $assunto = "Manutenção de VRPs (Válvula Redutora de Pressão) 405";
    $duracao = "02 dias trabalhados.";
    $garantia = "06 (seis) meses";
    $pagamento = "Após o termino do serviço 1 (uma) parcela de R$ 1.200,00 e mais 02 (duas) parcelas de R$ 1.200,00 a cada 30 dias, com boleto bancário.";
    $validade = "10 dias";
    $total = 3600.00;

    $obsBudget = $observations . "\n\nCUSTOS ADICIONAIS NECESSÁRIOS (SE HOUVER):\nDiafragma de 21/2” ... R$ 533,00\nManômetro ... R$ 150,00";

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
        2, // Marcia Imoto as per context of file owner if not specified, default to 2 or 1. Let's use 2 as in previous.
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
        ['desc' => 'Manutenção das 12 (doze) VRPs de 21/2”', 'qtd' => 1, 'val' => 3600.00, 'total' => 3600.00]
    ];

    $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([$orcId, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
    }
    echo "Items inserted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
