<?php
$zip = new ZipArchive();
$filename = __DIR__ . "/../storage/orcamentos_word/test_budget.docx";

if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
    exit("cannot open <$filename>\n");
}

$xmlContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>
<w:p><w:r><w:t>Orçamento para Condomínio Teste</w:t></w:r></w:p>
<w:p><w:r><w:t>CNPJ: 00.000.000/0001-00</w:t></w:r></w:p>
<w:p><w:r><w:t>Data: 2024-10-20</w:t></w:r></w:p>
<w:p><w:r><w:t>Serviço de Manutenção de Válvulas</w:t></w:r></w:p>
<w:p><w:r><w:t>Valor Total: R$ 1.500,00</w:t></w:r></w:p>
</w:body>
</w:document>';

$zip->addFromString("word/document.xml", $xmlContent);
$zip->addFromString("[Content_Types].xml", '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/></Types>');
$zip->close();

echo "Created $filename\n";
