<?php
/**
 * Created by PhpStorm.
 * User: webmaster
 * Date: 09/09/16
 * Time: 08:07
 */

try {
    $veraoTodo = new PDO('mysql:host=localhost;dbname=verao_to_do', 'root', 'webmaster123');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$veraoTodo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cpfArray = $veraoTodo->query("SELECT cpf FROM aluno GROUP BY cpf HAVING count(*) >= 2")->fetchAll(PDO::FETCH_COLUMN, 'cpf');

$alunosIdWithUsuarioId = [];




foreach ($cpfArray as $cpf) {

    $alunoLinhas = $veraoTodo->query("SELECT * FROM aluno WHERE cpf = $cpf")->fetchAll(PDO::FETCH_ASSOC);

    $alunosIdWithUsuarioId += getAlunosRepetidosWithUsuarioId($alunoLinhas);

}

deletaAlunosRepetidos($alunosIdWithUsuarioId);

$veraoTodo = null;







function deletaAlunosRepetidos($alunosIdsWithUsuarioId) {
    global $veraoTodo;
    $veraoTodo->beginTransaction();

    foreach ($alunosIdsWithUsuarioId as $alunoId => $usuarioId) {
        $veraoTodo->query("DELETE FROM aluno WHERE id = $alunoId");
        $veraoTodo->query("DELETE FROM usuario WHERE id = $usuarioId");
    }

    $veraoTodo->commit();
}


function getAlunosRepetidosWithUsuarioId($alunoLinhas) {

    $maxNotFalseCount = -1;
    $melhorAlunoId = -1;
    $alunoIds = [];

    foreach ($alunoLinhas as $aluno) {

        $alunoIds[$aluno['id']] = $aluno['usuario_id'];
        $notFalseCount = count(array_filter($aluno));

        if ($notFalseCount > $maxNotFalseCount) {
            $maxNotFalseCount = $notFalseCount;
            $melhorAlunoId = $aluno['id'];
        }
    }

    unset($alunoIds[$melhorAlunoId]);
    return $alunoIds;
}