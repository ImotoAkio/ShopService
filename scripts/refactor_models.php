<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    $updates = [
        // 1. Substituição de Coluna
        [
            'name_search' => 'Substituição de Coluna',
            'procedures_new' => null, // Keep original if null
            'desc_new' => "Substituição total, [ANDARES] andares da coluna em PPR 3” de AQ (água quente) por cobre classe A de 21/2”, para uma melhor condição de resistência mecânica. (do barrilete a [LOCAL_FIM])"
        ],
        // 2. Manutenção VRP 100 Varb
        [
            'name_search' => 'Manutenção e Substituição de VRP (Varb 100)',
            'desc_new' => "- Manutenção preventiva de [QTD] (duas) VRPs 100 e filtros em Y de 2”, para abastecer AF (agua Fria) instaladas em Quadro de Estação de Válvula Redutora duplo em cobre no teto da garagem de um prédio de [TOTAL_ANDARES] andares para atender até [ANDAR_LIMITE] andar; com PDS (pressão dinâmica de saída) em 4 kgf/cm2.\n- Manutenção preventiva de [QTD] (duas) VRPs 100 e filtros em Y de 2”, para abastecer AQ (agua quente), instaladas em Quadro de Estação de Válvula Redutora duplo em cobre no teto da garagem de um prédio de [TOTAL_ANDARES] andares para atender até [ANDAR_LIMITE] andar; com PDS (pressão dinâmica de saída) em 4 kgf/cm2.\n- Substituir [QTD] (duas) VRPs 100 e manutenção de filtros em Y de 21/2” para abastecer descargas devido corpo inferior furada por oxidação quente), instaladas em Quadro de Estação de Válvula Redutora duplo em cobre no teto da garagem de um prédio de [TOTAL_ANDARES] andares para atender até [ANDAR_LIMITE] andar; com PDS (pressão dinâmica de saída) em 4 kgf/cm2."
        ],
        // 3. Manutenção VRP 405 (Blocos/Complexa)
        [
            'name_search' => 'Manutenção de VRPs 405',
            'desc_new' => "[BLOCO_X]\nManutenção de [QTD] VRPs 405 de 21/2”, instaladas no [LOCAL] em Quadro de Estação Redutora duplo, em [MATERIAL] do prédio de [TOTAL_ANDARES] andares para atender [TIPO_AGUA] até o [ANDAR_LIMITE] andar, com PDS (pressão dinâmica de saída) em [PRESSAO] kgf. /cm2 e [QTD_ALIVIO] Válvula de Alívio 430 de 21/2” com PDS (pressão dinâmica de saída) em [PRESSAO_ALIVIO] kgf. /cm2."
        ],
        // 4. Manutenção VRP 25M
        [
            'name_search' => 'Manutenção VRP 25M',
            'desc_new' => "Manutenção de [QTD] VRPs 25M de [DIDAMETRO]” instaladas no [LOCAL_INSTALACAO] no prédio de [TOTAL_ANDARES] andares para atender do [ANDAR_INICIO] ao [ANDAR_FIM] andar com a PDS (pressão dinâmica de saída) em [PRESSAO] kgf. /cm2."
        ],
        // 5. Substituição de Registro
        [
            'name_search' => 'Substituição de Registro',
            'desc_new' => "Substituição de [QTD] registro geral do [LOCAL/APTO], gaveta bruto 11/4\", sem estanqueidade. Aplicar registro gaveta Deca, semi industrial, volante amarelo, 150 libras."
        ]
    ];

    foreach ($updates as $upd) {
        // Find ID
        $stmtSearch = $db->prepare("SELECT id, name FROM valve_models WHERE name LIKE ?");
        $stmtSearch->execute(['%' . $upd['name_search'] . '%']);
        $model = $stmtSearch->fetch();

        if ($model) {
            echo "Updating '{$model['name']}'...\n";
            $sql = "UPDATE valve_models SET service_description = ?";
            $params = [$upd['desc_new']];

            if (isset($upd['procedures_new'])) {
                $sql .= ", procedures = ?";
                $params[] = $upd['procedures_new'];
            }

            $sql .= " WHERE id = ?";
            $params[] = $model['id'];

            $stmtUpdate = $db->prepare($sql);
            $stmtUpdate->execute($params);
            echo "Updated.\n";
        } else {
            echo "Model matching '{$upd['name_search']}' not found.\n";
        }
    }

    echo "Refactoring complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
