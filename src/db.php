<?php

function executeStmt(PDOStatement $ps) {
    if ($ps->execute() === false) {
        throw new Exception('Databazova chyba: '.$ps->errorCode(). ' '.join(' ', $ps->errorInfo()));
    }
}

function executeSQL(PDO $pdo, $sql) {
    if ($pdo->exec($pdo, $sql)) {
        throw new Exception('Databazova chyba: '.$pdo->errorCode(). ' '.join(' ', $pdo->errorInfo()));
    }
}
