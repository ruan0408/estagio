<?php
/**
 * Created by PhpStorm.
 * User: webmaster
 * Date: 31/08/16
 * Time: 09:54
 */

try {
    $db_verao_todo = new PDO('mysql:host=localhost;dbname=verao_to_do', 'root', 'webmaster123');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$db_verao_todo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$tuples = $db_verao_todo->query("SELECT cpf, nome, email FROM aluno INNER JOIN usuario ON aluno.usuario_id = usuario.id")
    ->fetchAll(PDO::FETCH_ASSOC);

$cpf_email_db = [];
$name_email_db = [];
$student_data = [];
$certificates = [];
$name_email = [];

foreach ($tuples as $tuple) {
    $cpf_email_db[$tuple['cpf']] = $tuple['email'];
    $name_email_db[utf8_encode($tuple['nome'])] = $tuple['email'];
}




$handle = fopen("allStudentsData.csv", "r");
while (($line = fgets($handle)) !== false) {
    $data = explode(',', $line);

    $name = str_replace('"', '', $data[3]);
    $cpf = str_replace(['-', '.'], '', $data[4]);

    $cpf = (int) $cpf;
    $student_data[] = [$cpf, $name];
}


$handle = fopen("allCertificates.csv", "r");
while (($line = fgets($handle)) !== false)
    $certificates[] = trim($line);

fclose($handle);



foreach ($student_data as $data) {
    $cpf = $data[0];
    $name = strtoupper(iconv("UTF-8", "UTF8", $data[1]));

    if ($name == 'Marcio Dias da Silva')
        $name_email[$name] = 'vendomatematica@gmail.com';

    if ($name == 'Marco Antonio dos Santos')
        $name_email[$name] = 'isabella_salomao_santos@hotmail.com';

    if ($cpf != '' and key_exists($cpf, $cpf_email_db))
        $name_email[$name] = $cpf_email_db[$cpf];
    elseif ($name != '' and key_exists($name, $name_email_db))
        $name_email[$name] = $name_email_db[$name];
}


$aux = [];
foreach ($name_email as $name => $email) {
    $name = strtoupper(iconv("UTF-8", "ASCII//TRANSLIT", $name));
    $aux[$name] = $email;
}

$name_email = $aux;


foreach ($certificates as $pdf_name) {
    $string_parts = explode('_', $pdf_name);
    array_pop($string_parts);
    $student_name = strtoupper(implode(' ', $string_parts));

    if (key_exists($student_name, $name_email))
        $email = $name_email[$student_name];
    elseif ($student_name == 'RYAN MARCAL SALDANHA MAGA├▒A MARTINEZ')
        $email = 'marte1992@live.com';
    else
        continue;

    $txt_name = explode('.', $pdf_name)[0] . '.txt';

//    echo "mutt -s \"Verao IME-USP: sobre a emissao dos certificados do Verao 2016\"
//    $email -b leo@ime.usp.br -a certificados/$pdf_name < conteudo_emails/$txt_name" . PHP_EOL;
}

preencheConteudoEmails();

function preencheConteudoEmails() {

    $dir = new DirectoryIterator(dirname('conteudo_emails/.'));
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $string_parts = explode('_', $fileinfo->getFilename());
            array_pop($string_parts);
            $name = implode(' ', $string_parts);

            $message =  "Caro $name.
Agradecemos sua participacão no 45o. programa de Verão do IME-USP.
Em anexo, enviamos um PDF com o certificado de sua participacão.
            
Atenciosamente,
Comissão dos Cursos de Verão do IME-USP
http://www.ime.usp.br/verao";


            $file = fopen('conteudo_emails/' . $fileinfo->getFilename(), "w");

            fwrite($file, $message);
            fclose($file);
        }
    }
}