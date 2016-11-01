USE verao_to_do;

SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ALLOW_INVALID_DATES';

CREATE TABLE cidade LIKE verao.cidade;
INSERT cidade SELECT * FROM verao.cidade;

CREATE TABLE curso LIKE verao.curso;
INSERT curso SELECT * FROM verao.curso;

CREATE TABLE funcionario LIKE verao.funcionario;
INSERT funcionario SELECT * FROM verao.funcionario;

CREATE TABLE horario LIKE verao.horario;
INSERT horario SELECT * FROM verao.horario;

CREATE TABLE inscricao_turma LIKE verao.inscricao_turma;
INSERT inscricao_turma SELECT * FROM verao.inscricao_turma;

CREATE TABLE lote_retorno LIKE verao.lote_retorno;
INSERT lote_retorno SELECT * FROM verao.lote_retorno;

CREATE TABLE professor LIKE verao.professor;
INSERT professor SELECT * FROM verao.professor;

CREATE TABLE professores_turma LIKE verao.professores_turma;
INSERT professores_turma SELECT * FROM verao.professores_turma;

CREATE TABLE programa LIKE verao.programa;
INSERT programa SELECT * FROM verao.programa;

CREATE TABLE retorno LIKE verao.retorno;
INSERT retorno SELECT * FROM verao.retorno;

CREATE TABLE turma LIKE verao.turma;
INSERT turma SELECT * FROM verao.turma;
