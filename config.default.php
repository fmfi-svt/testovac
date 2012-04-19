<?php

$config = (object)array(
  // v demo mode sa prihlasuje umelym kodom a vysledky sa nikam neposielaju
  'demo_mode' => FALSE,

  // ci sa ma zakazat refreshovanie testovaca klavesovymi skratkami
  'disable_refresh' => TRUE,

  // <title>
  'title' => 'Prijímacia skúška',
);

require 'src/checkpid/VerhoeffChecker.php';
$pid_checker = new VerhoeffChecker();
