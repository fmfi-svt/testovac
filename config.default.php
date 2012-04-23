<?php

$config = (object)array(
  // v demo mode sa prihlasuje umelym kodom a vysledky sa nikam neposielaju
  'demo_mode' => FALSE,

  // ci sa ma zakazat refreshovanie testovaca klavesovymi skratkami
  'disable_refresh' => TRUE,
);

require 'src/exam/FlawExam.php';
$exam = new FlawExam();

require 'src/checkpid/VerhoeffChecker.php';
$pid_checker = new VerhoeffChecker();

function connect_db() {
  $dsn = 'mysql:host=localhost;dbname=mydbname';
  $username = 'myuser';
  $password = 'mypass';
  $options = array();
  return new PDO($dsn, $username, $password, $options);
}
$dbh = connect_db();
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
