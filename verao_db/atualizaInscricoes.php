<?php
/**
 * Created by PhpStorm.
 * User: webmaster
 * Date: 31/08/16
 * Time: 09:54
 */

try {
    $db_verao = new PDO('mysql:host=localhost;dbname=verao', 'root', 'webmaster123');
    $db_verao_todo = new PDO('mysql:host=localhost;dbname=verao_to_do', 'root', 'webmaster123');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$db_verao_todo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cpf_array = $db_verao_todo->query("SELECT cpf FROM aluno GROUP BY cpf HAVING count(*) >= 2")->fetchAll(PDO::FETCH_COLUMN, 'cpf');

$mapaUsuarioIdsParaMelhorUsuarioId = [];

foreach ($cpf_array as $cpf) {

    $alunoLinha = $db_verao_todo->query("SELECT * FROM aluno WHERE cpf = $cpf")->fetchAll(PDO::FETCH_ASSOC);

    $mapaUsuarioIdsParaMelhorUsuarioId += mapeiaUsuarioIdsParaMelhorUsuarioId($alunoLinha);

}

atualizaInscricoes($mapaUsuarioIdsParaMelhorUsuarioId);

$db_verao_todo = null;







function atualizaInscricoes($mapaUsuarioIdsParaMelhorUsuarioId) {
    global $veraoTodo;
    $veraoTodo->beginTransaction();

    foreach ($mapaUsuarioIdsParaMelhorUsuarioId as $usuarioId => $bestUsuarioId)
        $veraoTodo->query("UPDATE inscricao SET usuario_id = $bestUsuarioId WHERE usuario_id = $usuarioId ");

    $veraoTodo->commit();
}


function mapeiaUsuarioIdsParaMelhorUsuarioId($alunoRows) {

    $maxNotFalseCount = -1;
    $bestUsuarioId = -1;
    $usuarioIds = [];

    foreach ($alunoRows as $aluno) {

        array_push($usuarioIds, $aluno['usuario_id']);
        $notFalseCount = count(array_filter($aluno));

        if ($notFalseCount > $maxNotFalseCount) {
            $maxNotFalseCount = $notFalseCount;
            $bestUsuarioId = $aluno['usuario_id'];
        }
    }

    return array_fill_keys($usuarioIds, $bestUsuarioId);
}

function melhorUsuarioIdEOutrosUsuarioIds($alunoRows) {

    $maxNotFalseCount = -1;
    $bestUsuarioId = -1;
    $usuarioIds = [];

    foreach ($alunoRows as $aluno) {

        array_push($usuarioIds, $aluno['usuario_id']);
        $notFalseCount = count(array_filter($aluno));

        if ($notFalseCount > $maxNotFalseCount) {
            $maxNotFalseCount = $notFalseCount;
            $bestUsuarioId = $aluno['usuario_id'];
        }
    }

    unset($usuarioIds[array_search($bestUsuarioId, $usuarioIds)]);
    return array($bestUsuarioId, $usuarioIds);
}