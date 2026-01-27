<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$models = [
    [
        'name' => 'Modelo Padrão (Rosca)',
        'service_description' => 'Manutenção de [QTD] VRPs (válvula redutora de pressão) [MODELO]; instaladas em [LOCAL] [TORRE] com a PDS (pressão dinâmica de saída) em [PDS] para atender [ALCANCE].',
        'procedures' => "Fechamento do registro de entrada e saída da linha da Válvula Redutora.\n(Não nos responsabilizamos por avarias dos registros...)\n\n1- A ser executado no próprio local.\n2- Remoção do apoio abaixo da VRP em alvenaria para desmontagem geral do corpo da VRPs e Filtros em Y;\n3- Verificar o estado das peças internas e moveis e a parte interna do corpo inferior.\n4- Remover as incrustações de ferrugens internas de corpos da válvula e filtros;\n5- Remontar, testar a estanquiedade e regular a pressão de saída no Maximo em 4kgf/cm2 conforme NBR 5626 e NBR 7198 da ABNT.\n6- Aferir os manômetros da saída, e substituir se necessário; os manômetros da entrada e facultativo a sua instalação já que a pressão de entrada é sempre definida pela altura do prédio.\n7- Como a Estação possui somente uma VRP o prédio devera ficar parcialmente desabastecido durante o serviço.\n\n8- Mesmo que no aspecto externo a VRP estar aparentando boas condições, devido tempo sem manutenção após a abertura do corpo da VRP poderemos encontrar algumas peças internas que necessitem de troca como mola inferior, diafragma etc. ou ate mesmo da necessidade da sua substituição por completo. Esses problemas só poderão ser constatados efetivamente após a abertura do corpo da VRP.\nA execução da manutenção correta e periódica além de aumentar a segurança do sistema hidráulico do prédio minimiza eventuais ocorrências conforme descrito abaixo e aumenta o tempo de uso da VRP.\n9- Após termino do serviço colocar apoio de madeira apoiada na tubulação.",
        'observations' => "A deficiência no funcionamento das VRPs ou obstrução pode ocasionar:\n- Fuga de água. (Quando do banho a água esquenta ou esfria de mais, não conseguindo regular a temperatura da água).\n- Diminuição de água nas torneiras em alguns horários.\n- Impossibilidade de usar dois chuveiros simultâneos.\n- Falta de água em alguns horários.\n- Necessidade de aumentar a PDS (pressão dinâmica de saída) acima da regulamentar.\n- Barulho na rede (ruído).\n- Golpe de aliete.\n\nA pressão de saída acima da pressão regulamentar pode ocasionar:\nProvável rompimento de flexíveis, conexões de filtros de água, engate de maquinas de lavar roupa e louças;\nGolpe de aríete e ruídos na rede;\nPerda de água pela caixa acoplada aumentando a conta de água do condomínio. Desconforto na utilização das torneiras devido jato forte etc.\nAumento no consumo de água."
    ],
    [
        'name' => 'Modelo Flangeado (Industrial)',
        'service_description' => 'Manutenção Técnica Industrial de [QTD] Válvulas Flangeadas [MODELO]; Local: [LOCAL] - [TORRE]. Pressão Ajustada: [PDS].',
        'procedures' => "Procedimento Especial para Flanges:\n1- Bloqueio total da linha.\n2- Desaperto cruzado dos parafusos do flange.\n3- Substituição obrigatória das juntas de vedação.\n4- Inspeção de sedes e discos.\n5- Teste hidrostático de bancada (se necessário).",
        'observations' => "Obs: Válvulas flangeadas requerem maior tempo de cura para vedações líquidas (se utilizadas).\nA manutenção preventiva evita paradas não programadas."
    ]
];

try {
    $db->beginTransaction();
    // Use DELETE instead of TRUNCATE to stay safe within transaction
    $db->exec("DELETE FROM valve_models");
    // Reset Auto Increment if possible (optional)
    $db->exec("ALTER TABLE valve_models AUTO_INCREMENT = 1");

    $stmt = $db->prepare("INSERT INTO valve_models (name, service_description, procedures, observations) VALUES (?, ?, ?, ?)");

    foreach ($models as $model) {
        $stmt->execute([
            $model['name'],
            $model['service_description'],
            $model['procedures'],
            $model['observations']
        ]);
    }

    $db->commit();
    echo "Valve models re-seeded successfully with UTF-8.";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
