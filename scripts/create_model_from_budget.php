<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    $name = "Substituição de Coluna (PPR para Cobre)";

    $serviceDescription = "Substituição total, 32 andares da coluna em PPR 3” de AQ (água quente) por cobre classe A de 21/2”, para uma melhor condição de resistência mecânica. (do barrilete a Garagem1)";

    $procedures = "Substituir a partir do barrilete superior até a garagem 1, nos quadros das válvulas redutoras. 
Substituir um registro geral de 3” do início da prumada no barrilete superior.
Manter o mesmo traçado dentro do Shaffer e na mesma posição da coluna atual, devido falta de espaço para instalar junta de expansão imprescindível para absolver a dilatação linear do tubo em função do calor; e a fixação de suporte especiais como luva guia ponto fixo na tubulação para manter o alinhamento correto e delimitar o campo de trabalho das juntas de expansão.
As tubulações de cobre serão isoladas termicamente com material adequado como espuma de polietileno expandido.
Durante a execução do serviço os andares do prédio em trabalho deverão ficar desabastecido da agua quente durante o dia até as 16:00 horas, sendo restabelecido a partir desse horário. 
Nos andares pressurizado e superiores as derivações da coluna serão conectadas na tubulação em PPR de 2”.
No térreo abrir o forro para acessar a tubulação horizontal.
Abrir a passagem da tubulação por uma parede.
Passar por baixo da viga apoiada com, presa com parabolts.
O trabalho será feito substituindo 03 lances de coluna por dia, aproximadamente
- Isolamento e desligamento das caldeiras, fica a cargo da contratante.
- Reforçar a fixação da coluna instalando perfis metálicos com abraçadeira fixada nas vigas do prédio ancorada com parabolt.
Isolar a tubulação de cobre com material isolante para evitar o contato do cobre com o metal da abraçadeira e evitar corrosão eletrolítica.
No 18º andar instalar um registro de gaveta bruto de 21/2” na prumada para não necessitar de desabastecer o prédio inteiro quando da execução do serviço nos andares do sistema baixo e intermediário. 
Sem correção do forro de gesso, correção de alvenaria com acabamento desempenado sem pintura e sem colocação de azulejo.";

    // Append Payment/Values info to Observations, as templates don't usually hold specific pricing or dates, 
    // but the user wants 'Models based on budgets sent'.
    // We will keep the general observation text.
    $observations = "Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada;
Horário de trabalho em dias úteis, segunda á Sexta das 09h00min ás 17h00min h com 1 h de almoço.
Diariamente os locais de serviço serão limpos, deixando em condições de uso.
É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.
É de responsabilidade de a contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção, designar um local para guarda de ferramentas e material, bem com um vestiário e sanitários para uso do nosso pessoal.  
O prazo para execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.
Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.
Todos os serviços serão executados: 
Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.
Empresa possuidora de seguro de sinistro de obra.
Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.
Workshop nas fabricas.
Cumprindo com as exigências das NBR 5626 com garantia de 01 (um) ano. 
Empresa desde 1982.";

    // Check if exists
    $stmtCheck = $db->prepare("SELECT id FROM valve_models WHERE name = ?");
    $stmtCheck->execute([$name]);
    if ($stmtCheck->fetch()) {
        echo "Template '$name' already exists.\n";
    } else {
        echo "Inserting Template '$name'...\n";
        $stmt = $db->prepare("INSERT INTO valve_models (name, service_description, procedures, observations) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $serviceDescription, $procedures, $observations]);
        echo "Template inserted via ID " . $db->lastInsertId() . ".\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
