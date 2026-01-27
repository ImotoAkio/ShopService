<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // 1. Check or Insert Client
    $clientName = "Condomínio Ladresse";
    $cnpj = "21.329.901/0001-00";

    $stmtCheck = $db->prepare("SELECT id FROM clientes WHERE name = ? OR cnpj = ?");
    $stmtCheck->execute([$clientName, $cnpj]);
    $client = $stmtCheck->fetch();

    if ($client) {
        $clientId = $client['id'];
        echo "Client found with ID: $clientId\n";
    } else {
        echo "Creating new client...\n";
        $stmtInsertClient = $db->prepare("INSERT INTO clientes (
            name, address, cnpj, 
            responsavel, zelador_nome, zelador_tel, zelador_email,
            admin_email, email, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        // Mapping Data
        // A/C Sr. Marcos (zelador) Tel: (11) 94209-6707
        // Email zeladorialadresse@gmail.com
        // E-mail: ladressezeladoria@gmail.com
        // E-mail orcamento@graiche.com.br
        // Financeiro larissa@graiche.com.br -> Putting in admin_email or email

        $stmtInsertClient->execute([
            $clientName,                                // name
            "Rua: Diogo Jacome, 553, Vila Nova Conceição – São Paulo - SP", // address
            $cnpj,                                     // cnpj
            "Sr. Marcos",                              // responsavel (using Marcos as main contact)
            "Marcos",                                  // zelador_nome
            "(11) 94209-6707",                         // zelador_tel
            "zeladorialadresse@gmail.com",             // zelador_email
            "orcamento@graiche.com.br",                // admin_email (using Graiche as admin/admin contact)
            "ladressezeladoria@gmail.com"              // email (main email)
        ]);
        $clientId = $db->lastInsertId();
        echo "Client created with ID: $clientId\n";
    }

    // 2. Prepare Budget Data
    $assunto = "Substituição de coluna.";

    $servicoDescricao = "Substituição total, 32 andares da coluna em PPR 3” de AQ (água quente) por cobre classe A de 21/2”, para uma melhor condição de resistência mecânica. (do barrilete a Garagem1)";

    $procedimentos = "Substituir a partir do barrilete superior até a garagem 1, nos quadros das válvulas redutoras. 
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

    $duracao = "32 dias trabalhados.";
    $validade = "10 dias";
    $garantia = "01 (um) ano";

    $formaPagamento = "Após o início da atividade 01 (uma) parcela de R$ 15.600,00 e mais 04 (quatro) parcelas de R$ 15.600,00 a cada 30 dias subsequentes, com nota fiscal e boleto bancário.";

    $observacoes = "Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada;
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
Empresa desde 1982.

MATERIAL: 
Material comprado em Dez2025: entregue em 13/01/2026 Merc nf 191752-R$ 58477,38
14/01/2026 Ze /jair conferencia de ,material – gerou devolução e novas compras
16/01/2026 Marcia dev material na Merc-NF 15190 R$ 4587,45
20/01/2026 chegada material Merc
21/01/2026 ZE felipe
Material, poderá ser comprado pela contratada com faturado e pago pelo Condomínio.
R$ 85.000,00 (estimativa)";

    $total = 78000.00;

    // Check if budget already exists (deduplication by subject + client within recent time? or just always insert new)
    // For this task, we'll insert a new one.

    echo "Inserting Budget...\n";
    $stmtOrc = $db->prepare("INSERT INTO orcamentos (
        client_id, user_id, status, total, assunto, 
        servico_descricao, procedimentos, duracao, garantia, 
        forma_pagamento, observacoes, validade, created_at
    ) VALUES (
        ?, ?, 'Pendente', ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, NOW()
    )");

    // User ID 2 (Marcia Imoto)
    $userId = 2;

    $stmtOrc->execute([
        $clientId,
        $userId,
        $total,
        $assunto,
        $servicoDescricao,
        $procedimentos,
        $duracao,
        $garantia,
        $formaPagamento,
        $observacoes,
        $validade
    ]);

    $orcamentoId = $db->lastInsertId();
    echo "Budget created with ID: $orcamentoId\n";

    // 3. Insert Items
    $items = [
        ['desc' => 'Mão de Obra', 'qtd' => 1, 'val' => 79000.00, 'total' => 79000.00],
        ['desc' => 'Desconto', 'qtd' => 1, 'val' => -1000.00, 'total' => -1000.00]
    ];

    $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");

    foreach ($items as $item) {
        $stmtItem->execute([$orcamentoId, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
        echo "Inserted item: {$item['desc']}\n";
    }

    echo "Done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
