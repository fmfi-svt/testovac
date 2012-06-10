<?php
require_once 'db_interface.php';

function connect_db() {
    $dsn = array(
        'phptype' => 'mysql',
        'username' => 'root',
        'password' => '',
        'hostspec' => 'localhost',
        'database' => 'testovac',
    );

    $options = array(
        'debug' => 2,
        'portability' => DB_PORTABILITY_ALL,
    );
    
    $dbh = & DB::connect($dsn, $options);
    $dbh->query('SET NAMES UTF8;');
    return $dbh;
}

?>
