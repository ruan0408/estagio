<?php
/**
 * Created by PhpStorm.
 * User: webmaster
 * Date: 11/10/16
 * Time: 08:29
 */

$nomes = ['PEDRO IVO GOMES DE FARIA', 'DANIEL TSUTOMU OBARA', 'ELINARDO', 'JULIA CHAVES SILVA'];

$cpfs = [12345678925, 34303364819, 41894874811, 36491778894, 6819520832];

$emails = ['ruan.cocito@gmail.com', 'ruan.costa@usp.br', 'a@hotmail.com', 'luizinha123@bol.com'];

function obter_primeiro_nome($nome) {
    $palavras = explode(' ', $nome);
    return ucfirst(strtolower($palavras[0]));
}

assert(obter_primeiro_nome($nomes[0]) == 'Pedro');
assert(obter_primeiro_nome($nomes[1]) == 'Daniel');
assert(obter_primeiro_nome($nomes[2]) == 'Elinardo');
assert(obter_primeiro_nome($nomes[3]) == 'Julia');

function ofusca_cpf($cpf) {
    $cpf_str = "$cpf";
    $cpf_formatted = substr($cpf_str, 0, 3) . '.' . substr($cpf_str, 3, 3) . '.' .substr($cpf_str, 6, 3) . '-' .substr($cpf_str, -2);

    $cpf_ofuscado = substr_replace($cpf_formatted, '***', 4, 3);
    $cpf_ofuscado = substr_replace($cpf_ofuscado, '***', 8, 3);

    return $cpf_ofuscado;
}

assert(ofusca_cpf($cpfs[0]) == '123.***.***-25');
assert(ofusca_cpf($cpfs[1]) == '343.***.***-19');
assert(ofusca_cpf($cpfs[2]) == '418.***.***-11');
assert(ofusca_cpf($cpfs[3]) == '364.***.***-94');
assert(ofusca_cpf($cpfs[4]) == '681.***.***-32');


function ofusca_email($email) {
    $partes = explode('@', $email);

    if (strlen($partes[0]) > 3) {
        $asteriscos = str_repeat('*', strlen($partes[0]) - 2);
        $partes[0] = substr_replace($partes[0], $asteriscos, 1, -1);
    } else {
        $partes[0] = str_repeat('*',strlen($partes[0]));
    }

    $email_ofuscado = implode('@', $partes);
    return $email_ofuscado;
}

assert(ofusca_email($emails[0]) == 'r*********o@gmail.com');
assert(ofusca_email($emails[1]) == 'r********a@usp.br');
assert(ofusca_email($emails[2]) == '*@hotmail.com');
assert(ofusca_email($emails[3]) == 'l*********3@bol.com');


function valida_nome($nome) {
    $partes = explode(' ', $nome);
    if (count($partes) < 2) return false;

    foreach ($partes as $parte)
        if (strlen($parte) < 3) return false;

    return true;
}

assert(valida_nome($nomes[0]) == false);
assert(valida_nome($nomes[1]) == true);
assert(valida_nome($nomes[2]) == false);
assert(valida_nome($nomes[3]) == true);



