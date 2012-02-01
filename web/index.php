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


if ($config->require_https) {
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    // HTTPS je zapnute, ide sa dalej
    header('Strict-Transport-Security: max-age=500');
  }
  else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // mozme redirectnut z http na https
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
    exit();
  }
  else {
    // redirectom by vznikol GET, takze neredirectujeme, ale forbidneme
    header('HTTP/1.1 403 Forbidden');
    exit();
  }
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
