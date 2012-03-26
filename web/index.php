<?php

ini_set('display_errors', FALSE);
chdir('..');
require 'config.php';


function json_out($data) {
  header('Content-Type: application/json; charset=UTF-8');
  print json_encode($data);
}
function json_die($errtype) {
  json_out(array('error' => $errtype));
  die();
}


if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
  // http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
  header('Strict-Transport-Security: max-age=500');
}


$action = empty($_POST['action']) ? 'front' : $_POST['action'];
$actions = array(
  'front' => 'src/front.php',
  'login' => 'src/login.php',
  'save' => 'src/save.php',
  'close' => 'src/close.php',
);
if (!isset($actions[$action])) {
  json_die('unknown action');
}
require $actions[$action];
