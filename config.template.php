<?php

function connect_db() {
    
    $dbname = 'testovac';
    $host = 'localhost';
    $user = 'root';
    $pass = '';

    $dbh = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $dbh->query('SET NAMES UTF8;');
    return $dbh;
}

?>
