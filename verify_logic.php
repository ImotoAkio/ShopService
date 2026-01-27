<?php
// Mock Date calculation logic
$validade_meses = 6;
$data_proxima_manutencao = date('Y-m-d', strtotime("+$validade_meses months"));
echo "Hoje: " . date('Y-m-d') . "\n";
echo "Validade: $validade_meses meses\n";
echo "Próxima: $data_proxima_manutencao\n";

if ($data_proxima_manutencao == date('Y-m-d', strtotime('+6 months'))) {
    echo "Lógica de data correta.\n";
} else {
    echo "Erro na lógica de data.\n";
}

// Simulating logic for alert (Expired or Today)
$alerts = [];
$mock_db_date = date('Y-m-d', strtotime('-1 day')); // Yesterday
if ($mock_db_date <= date('Y-m-d')) {
    echo "Alerta deve aparecer para data: $mock_db_date (Ontem)\n";
}

$mock_db_date_future = date('Y-m-d', strtotime('+1 day')); // Tomorrow
if ($mock_db_date_future <= date('Y-m-d')) {
    echo "Alerta NÃO deve aparecer para data: $mock_db_date_future (Amanhã)\n";
} else {
    echo "Lógica de alerta correta (ignora futuro).\n";
}
