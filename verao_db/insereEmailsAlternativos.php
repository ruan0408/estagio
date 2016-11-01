<?php
/**
 * Created by PhpStorm.
 * User: webmaster
 * Date: 06/09/16
 * Time: 16:54
 */

try {
    $db_verao = new PDO('mysql:host=localhost;dbname=verao', 'root', 'webmaster123');
    $db_verao_todo = new PDO('mysql:host=localhost;dbname=verao_to_do', 'root', 'webmaster123');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$db_verao_todo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cpf_array = $db_verao_todo->query("SELECT cpf FROM aluno GROUP BY cpf HAVING count(*) >= 2")->fetchAll(PDO::FETCH_COLUMN, 'cpf');

$usuarioIdToEmails = [];

foreach ($cpf_array as $cpf) {

    $alunoLinha = $db_verao_todo->query("SELECT * FROM aluno WHERE cpf = $cpf")->fetchAll(PDO::FETCH_ASSOC);

    list($melhorUsuarioId, $outrosUsuarioIds) = melhorUsuarioIdEOutrosUsuarioIds($alunoLinha);

    $usuarioIdToEmails[$melhorUsuarioId] = getEmailsAlternativos($outrosUsuarioIds);

}
insereEmailsAlternativos($usuarioIdToEmails);

$db_verao_todo = null;







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

function getEmailsAlternativos($outrosUsuarioIds) {
    global $veraoTodo;

    $query = "SELECT email FROM usuario WHERE";
    foreach ($outrosUsuarioIds as $usuarioId)
        $query .= " id = $usuarioId OR";

    $query = preg_replace('/\sOR$/', '', $query);

    $emails_array = $veraoTodo->query($query)->fetchAll(PDO::FETCH_COLUMN, 'email');

    array_filter($emails_array);
    return $emails_array;


}

function insereEmailsAlternativos($usuarioIdToEmails) {
    global $veraoTodo;

    $veraoTodo->beginTransaction();

    foreach ($usuarioIdToEmails as $usuarioId => $emails) {
        var_dump($usuarioId);
        var_dump($emails);

        if (!array_key_exists(1, $emails))
            $veraoTodo->query("UPDATE usuario SET email_alter1 = '$emails[0]', email_alter2 = NULL WHERE id = $usuarioId");
        else
            $veraoTodo->query("UPDATE usuario SET email_alter1 = '$emails[0]', email_alter2 = '$emails[1]' WHERE id = $usuarioId");
    }

    $veraoTodo->commit();

}