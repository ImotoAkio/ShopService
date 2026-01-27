<?php
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

$name = "Substituição Válvula Redutora de Pressão (VRP) Emetti e registros";

// HTML Formatted Content
$service_description = '
<p><strong>Agua Fria</strong></p>
<p>Substituir 03 (três) VRPs italiana e 02 (dois) registros de gaveta bruto de 1 1/2” instalados nos quadros duplos em PVC marrom, do 10º andar:</p>
<ul>
    <li>Substituir 01(registro) gaveta 1 1/2 ao lado do filtro.</li>
    <li><strong>Quadro Superior:</strong> Substituir as 2 vrps. Substituir o registro de entrada e saída da linha inferior.</li>
    <li><strong>Quadro Inferior:</strong> substituir VRP da linha inferior.</li>
</ul>';

$procedures = '
<ul>
    <li>Aplicar VRPs da Emmetti na mesma bitola.</li>
    <li>Aplicar registo de gaveta bruto de 1 1/2” da DECA volante amarelo.</li>
    <li>Após teste de carga regular a PDS (pressão dinâmica de saída) em 2 kgf. /cm2 conforme NBR 5626 da ABNT.</li>
    <li>Na troca dos registros será necessário drenar a coluna por inteiro e o prédio deverá ficar desabastecido por aproximadamente 7 horas:
        <ul>
            <li>2 horas o prédio todo sem agua Fria</li>
            <li>5 horas do 10º ao 7º andar sem agua Fria</li>
        </ul>
    </li>
</ul>';

// Observations includes detailed financial info which is usually custom, but keeping it as part of the template as requested.
$observations = '
<p><strong>Duração do serviço:</strong> 01 dia trabalhado.</p>
<hr>

<p><strong>Custo sem ART: Validade da Proposta 10 dias</strong></p>
<table class="table table-bordered table-sm" style="width: auto;">
    <tbody>
        <tr>
            <td>Mão de obra</td>
            <td>R$ 2.000,00</td>
        </tr>
        <tr>
            <td>Registros</td>
            <td>R$ 500,00</td>
        </tr>
        <tr>
            <td>Pecas adequação válvulas</td>
            <td>R$ 1.500,00</td>
        </tr>
        <tr>
            <td><strong>Total Mik</strong></td>
            <td><strong>R$ 4.000,00</strong></td>
        </tr>
        <tr>
            <td>03 VRP Emmetti de 1 1/2"</td>
            <td>R$ 3.101,04 – fatura 30/60, da Emmeti para o Condomínio.</td>
        </tr>
        <tr>
            <td><strong>Total Geral</strong></td>
            <td><strong>R$ 7.101,04</strong></td>
        </tr>
    </tbody>
</table>
<p>(Tognini R$ 699,00 +230+)</p>
<br>

<p><strong>Forma de Pagamento: (sugestão)</strong><br>
VRPs Emmeti R$ 3.101,04 - fatura 30/60 dias da Emmeti para o Condomínio.<br>
Após o término do serviço 01 (uma) parcela de R$ 1.334,00 e mais 02 (duas) parcela de R$ 1.333,00 para 30 dias com boleto bancário e nota fiscal.
</p>
<br>

<p><strong>Considerações:</strong></p>
<ul>
    <li>Todos os encargos sociais, administrativos, pessoais de segurança são de responsabilidade exclusiva da Contratada.</li>
    <li>O objetivo desta proposta é a substituição de 03 VRP e 02 registros.</li>
    <li>Horário de trabalho em dias úteis, segunda á sexta das 09h00min ás 17h00min h com 1 h de almoço.</li>
    <li>É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.</li>
    <li>É de responsabilidade de a contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção, designar um local para guarda de ferramentas e material, bem com um vestiário e sanitários para uso do nosso pessoal.</li>
    <li>O prazo para a execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.</li>
    <li>Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.</li>
</ul>

<div style="background-color: #FFFF00; padding: 10px;">
    <strong>Todos os serviços serão executados:</strong><br>
    Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.<br>
    Empresa possuidora de seguro de sinistro de obra.<br>
    Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.<br>
    Workshop nas fabricas.<br>
    Cumprindo com as exigências da NBR 5626 com garantia de 01 (um) ano.
    Empresa desde 1982.
</div>
';

try {
    $stmt = $db->prepare("INSERT INTO valve_models (name, service_description, procedures, observations) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $service_description, $procedures, $observations]);
    echo "Modelo inserido com sucesso: " . $name . PHP_EOL;
} catch (Exception $e) {
    echo "Erro ao inserir: " . $e->getMessage() . PHP_EOL;
}
