<?php
require_once 'app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance()->getConnection();

    $clients = [
        [
            'name' => 'Condomínio Edifício Solar das Acácias',
            'documento' => '12.345.678/0001-90',
            'email' => 'solar.acacias@email.com',
            'phone' => '(11) 3333-4444',
            'address' => 'Rua das Flores, 123 - Jardins, São Paulo - SP',
            'responsavel' => 'João Silva',
            'cargo' => 'Zelador',
            'telefone2' => '(11) 99999-8888',
            'cnpj' => '12.345.678/0001-90',
            'zelador_nome' => 'João Silva',
            'zelador_tel' => '(11) 98888-7777',
            'zelador_tel2' => '(11) 3333-5555',
            'zelador_email' => 'joao.zelador@email.com',
            'sindico_nome' => 'Maria Oliveira',
            'sindico_tel' => '(11) 97777-6666',
            'sindico_tel2' => '',
            'sindico_email' => 'maria.sindica@email.com',
            'admin_nome' => 'Administradora Confiança',
            'admin_tel' => '(11) 4444-5555',
            'admin_tel2' => '',
            'admin_email' => 'contato@confianca.com.br'
        ],
        [
            'name' => 'Condomínio Residencial Parque das Águas',
            'documento' => '98.765.432/0001-10',
            'email' => 'parque.aguas@email.com',
            'phone' => '(11) 3000-2000',
            'address' => 'Av. das Nações Unidas, 5000 - Pinheiros, São Paulo - SP',
            'responsavel' => 'Pedro Santos',
            'cargo' => 'Gerente Predial',
            'telefone2' => '(11) 91111-2222',
            'cnpj' => '98.765.432/0001-10',
            'zelador_nome' => 'Antônio Costa',
            'zelador_tel' => '(11) 92222-3333',
            'zelador_tel2' => '',
            'zelador_email' => 'antonio.costa@email.com',
            'sindico_nome' => 'Ana Pereira',
            'sindico_tel' => '(11) 93333-4444',
            'sindico_tel2' => '(11) 3000-2001',
            'sindico_email' => 'ana.pereira@email.com',
            'admin_nome' => 'Gestão Total Adm',
            'admin_tel' => '(11) 5000-6000',
            'admin_tel2' => '',
            'admin_email' => 'atendimento@gestaototal.com.br'
        ],
        [
            'name' => 'Empresa Comercial Ltda',
            'documento' => '11.222.333/0001-44',
            'email' => 'comercial@empresa.com',
            'phone' => '(11) 2222-3333',
            'address' => 'Rua Augusta, 1000 - Consolação, São Paulo - SP',
            'responsavel' => 'Carlos Souza',
            'cargo' => 'Diretor',
            'telefone2' => '',
            'cnpj' => '11.222.333/0001-44',
            'zelador_nome' => '',
            'zelador_tel' => '',
            'zelador_tel2' => '',
            'zelador_email' => '',
            'sindico_nome' => '',
            'sindico_tel' => '',
            'sindico_tel2' => '',
            'sindico_email' => '',
            'admin_nome' => '',
            'admin_tel' => '',
            'admin_tel2' => '',
            'admin_email' => ''
        ],
        [
            'name' => 'Condomínio Jardins do Sul',
            'documento' => '55.666.777/0001-88',
            'email' => 'jardins.sul@email.com',
            'phone' => '(11) 5555-6666',
            'address' => 'Rua Vergueiro, 7000 - Vila Mariana, São Paulo - SP',
            'responsavel' => 'Roberto Lima',
            'cargo' => 'Zelador',
            'telefone2' => '(11) 99911-2233',
            'cnpj' => '55.666.777/0001-88',
            'zelador_nome' => 'Roberto Lima',
            'zelador_tel' => '(11) 99911-2233',
            'zelador_tel2' => '',
            'zelador_email' => 'roberto.zelador@email.com',
            'sindico_nome' => 'Fernanda Martins',
            'sindico_tel' => '(11) 98877-6655',
            'sindico_tel2' => '',
            'sindico_email' => 'fernanda.martins@email.com',
            'admin_nome' => 'Lello Condomínios',
            'admin_tel' => '(11) 4004-0000',
            'admin_tel2' => '',
            'admin_email' => 'contato@lello.com.br'
        ]
    ];

    $stmt = $db->prepare("INSERT INTO clientes (
        name, documento, email, phone, address, responsavel, cargo, telefone2, cnpj,
        zelador_nome, zelador_tel, zelador_tel2, zelador_email,
        sindico_nome, sindico_tel, sindico_tel2, sindico_email,
        admin_nome, admin_tel, admin_tel2, admin_email
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?
    )");

    foreach ($clients as $client) {
        // Check if exists
        $check = $db->prepare("SELECT id FROM clientes WHERE documento = ?");
        $check->execute([$client['documento']]);
        if ($check->rowCount() > 0) {
            echo "Skipped {$client['name']} (already exists)\n";
            continue;
        }

        $stmt->execute([
            $client['name'],
            $client['documento'],
            $client['email'],
            $client['phone'],
            $client['address'],
            $client['responsavel'],
            $client['cargo'],
            $client['telefone2'],
            $client['cnpj'],
            $client['zelador_nome'],
            $client['zelador_tel'],
            $client['zelador_tel2'],
            $client['zelador_email'],
            $client['sindico_nome'],
            $client['sindico_tel'],
            $client['sindico_tel2'],
            $client['sindico_email'],
            $client['admin_nome'],
            $client['admin_tel'],
            $client['admin_tel2'],
            $client['admin_email']
        ]);
        echo "Inserted {$client['name']}\n";
    }

    echo "Client seeding completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
