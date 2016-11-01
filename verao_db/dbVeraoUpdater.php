<?php
/**
 * Created by PhpStorm.
 * User: webmaster
 * Date: 16/08/16
 * Time: 09:24
 */

try {
    $db_verao = new PDO('mysql:host=localhost;dbname=verao', 'root', 'webmaster123');
    $db_verao_todo = new PDO('mysql:host=localhost;dbname=verao_to_do', 'root', 'webmaster123');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$db_verao_todo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db_verao_todo->prepare("INSERT INTO aluno (
          id, email, nome, cpf, nome_titular_cpf, data_nascimento, nome_mae, sexo, usuario_id,
          logradouro_residencia, numero_residencia, complemento_residencia,
            bairro_residencia, cidade_residencia, estado_residencia, codpas_pais_residencia,
          numero_telefone_fixo, numero_telefone_celular,
          cidade_nascimento, estado_nascimento, codpas_pais_nascimento,
          id_tipo_documento, numero_documento, data_expedicao_documento, data_validade_documento,
            orgao_expedidor_documento, estado_expedidor_documento, codpas_pais_expedidor_documento
          )
          VALUES (:id, :email, :nome, :cpf, :nome_titular_cpf, :data_nascimento, :nome_mae, :sexo, :usuario_id,
                  :logradouro, :numero, :complemento, :bairro, :cidade, :estado, :codpas_residencia,
                  :telefone_fixo, :celular,
                  :cidade_nascimento, :estado_nascimento, :codpas_nascimento,
                  :id_tipo_documento, :numero_documento, :data_expedicao_documento, :data_validade_documento,
                    :orgao_expedidor_documento, :estado_expedidor_documento, :codpas_expedidor_documento
                 )"
);

$paises_row = $db_verao_todo->query('SELECT id, nome, codpas FROM pais')->fetchAll();

$paises_map_name_codpas = [];
$paises_map_id_codpas = [];
foreach ($paises_row as $row) {
    $paises_map_name_codpas[$row['nome']] = (int)$row['codpas'];
    $paises_map_id_codpas[$row['id']] = (int) $row['codpas'];
}

$alunos = $db_verao->query('SELECT * FROM verao.aluno');

$db_verao_todo->beginTransaction();
while($aluno = $alunos->fetch(PDO::FETCH_ASSOC)) {

//    if ($aluno['id'] != 12189) continue;

    $enderecos = $telefones = $nacionalidades = $documentos = null;
    $endereco = $telefone = $nacionalidade = $documento = null;

    $documento = array_fill_keys(array('documento_id', 'registro', 'data_de_expedicao',
        'data_de_validade', 'orgao_expedidor', 'estado_documento',
        'codpas_expedidor_documento'), null);

    $enderecos = $db_verao->query("SELECT * FROM endereco_aluno WHERE id = $aluno[endereco_id]");
    $telefones = $db_verao->query("SELECT * FROM telefone_aluno WHERE aluno_id = $aluno[id]");
    $nacionalidades = $db_verao->query("SELECT * FROM nacionalidade_aluno WHERE id = $aluno[nacionalidade_id]");
    $documentos = $db_verao->query("SELECT * FROM identificacao_aluno WHERE id = $aluno[identificacao_id]");

    if ($enderecos) $endereco = $enderecos->fetch(PDO::FETCH_ASSOC);
    if ($telefones) $telefone = $telefones->fetch(PDO::FETCH_ASSOC);
    if ($nacionalidades) $nacionalidade = $nacionalidades->fetch(PDO::FETCH_ASSOC);
    if ($documentos) $documento = $documentos->fetch(PDO::FETCH_ASSOC);

    $sexo = $aluno['sexo'][0];

    // Informação não contida no esquema antigo. Deixamos aqui todos por default morando no Brasil.
    $codpas_pais_residencia = 1;

    if ($nacionalidade and key_exists('pais_id', $nacionalidade) and
        key_exists($nacionalidade['pais_id'], $paises_map_id_codpas)) {
        $codpas_nascimento = $paises_map_id_codpas[$nacionalidade['pais_id']];
    } else $codpas_nascimento = null;

    if ($documento and key_exists('pais_documento', $documento) and
        key_exists($documento['pais_documento'], $paises_map_name_codpas)) {
        $codpas_expedidor_documento = $paises_map_name_codpas[$documento['pais_documento']];
    } else $codpas_expedidor_documento = null;

//    var_dump($documento);

    if ($documento['data_de_expedicao'] == '0000-00-00')
        $documento['data_de_expedicao'] = null;

    if ($documento['data_de_validade'] == '0000-00-00')
        $documento['data_de_validade'] = null;

    if ($documento['documento_id'] <= 0)
        $documento['documento_id'] = null;

//    var_dump($documento);

    $stmt->execute([
        ':id' => $aluno['id'],
        ':email' => $aluno['email'],
        ':nome' => $aluno['nome'],
        ':cpf' => $aluno['cpf'],
        ':nome_titular_cpf' => $aluno['nome_do_titular'],
        ':data_nascimento' => $aluno['data_de_nascimento'],
        ':nome_mae' => $aluno['nome_da_mae'],
        ':sexo' => $sexo,
        ':usuario_id' => $aluno['usuario_id'],

        ':logradouro' => $endereco['endereco'],
        ':numero' => $endereco['numero'],
        ':complemento' => $endereco['complemento'],
        ':bairro' => $endereco['bairro'],
        ':cidade' => $endereco['cidade_str'],
        ':estado' => $endereco['estado_str'],
        ':codpas_residencia' => $codpas_pais_residencia,

        ':telefone_fixo' => $telefone['numtel1'],
        ':celular' => $telefone['numtel2'],

        ':cidade_nascimento' => $nacionalidade['cidade'],
        ':estado_nascimento' => $nacionalidade['estado'],
        ':codpas_nascimento' => $codpas_nascimento,

        ':id_tipo_documento' => $documento['documento_id'],
        ':numero_documento' => $documento['registro'],
        ':data_expedicao_documento' => $documento['data_de_expedicao'],
        ':data_validade_documento' => $documento['data_de_validade'],
        ':orgao_expedidor_documento' => $documento['orgao_expedidor'],
        ':estado_expedidor_documento' => $documento['estado_documento'],
        ':codpas_expedidor_documento' => $codpas_expedidor_documento
    ]);
}

$db_verao_todo->commit();
$db_verao = $db_verao_todo = null;

//$sexo = $aluno['sexo'][0];
//# essa informação não existe no banco anterior. id do brasil por default
//$id_pais_residencia = 1;
//
//$stmt->bindParam(':id', $aluno['id']);
//$stmt->bindParam(':email', $aluno['email']);
//$stmt->bindParam(':nome', $aluno['nome']);
//$stmt->bindParam(':cpf', $aluno['cpf']);
//$stmt->bindParam(':nome_titular_cpf', $aluno['nome_do_titular']);
//$stmt->bindParam(':data_nascimento', $aluno['data_de_nascimento']);
//$stmt->bindParam(':nome_mae', $aluno['nome_da_mae']);
//$stmt->bindParam(':sexo', $sexo);
//$stmt->bindParam(':usuario_id', $aluno['usuario_id']);
//
//$stmt->bindParam(':logradouro', $endereco['endereco']);
//$stmt->bindParam(':numero', $endereco['numero']);
//$stmt->bindParam(':complemento', $endereco['complemento']);
//$stmt->bindParam(':bairro', $endereco['bairro']);
//$stmt->bindParam(':cidade', $endereco['cidade_str']);
//$stmt->bindParam(':estado', $endereco['estado_str']);
//$stmt->bindParam(':codpas_residencia', $id_pais_residencia);
//
//$stmt->bindParam(':telefone_fixo', $telefone['numtel1']);
//$stmt->bindParam(':celular', $telefone['numtel2']);
//
//$stmt->bindParam(':cidade_nascimento', $nacionalidade['cidade']);
//$stmt->bindParam(':estado_nascimento', $nacionalidade['estado']);
//$stmt->bindParam(':codpas_nascimento', $codpas_nascimento);
//
//$stmt->bindParam(':id_tipo_documento', $documento['documento_id']);
//$stmt->bindParam(':numero_documento', $documento['registro']);
//$stmt->bindParam(':data_expedicao_documento', $documento['data_de_expedicao']);
//$stmt->bindParam(':data_validade_documento', $documento['data_de_validade']);
//$stmt->bindParam(':orgao_expedidor_documento', $documento['orgao_expedidor']);
//$stmt->bindParam(':estado_expedidor_documento', $documento['estado_documento']);
//
//if (key_exists('pais_documento', $documento) and
//    key_exists($documento['pais_documento'], $paises_map_name_codpas)) {
//    $codpas_expedidor_documento = $paises_map_name_codpas[$documento['pais_documento']];
//} else $codpas_expedidor_documento = null;
//
//$stmt->bindParam(':codpas_expedidor_documento', $codpas_expedidor_documento);
//
//if (key_exists('pais_id', $nacionalidade) and
//    key_exists($nacionalidade['pais_id'], $paises_map_id_codpas)) {
//    $codpas_nascimento = $paises_map_id_codpas[$nacionalidade['pais_id']];
//} else $codpas_nascimento = null;
//
//$stmt->execute();





