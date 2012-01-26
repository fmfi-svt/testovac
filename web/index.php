<?php

ini_set('display_errors', FALSE);
chdir('..');
require 'config.php';


function json_die($errtype) {
  header('Content-Type: application/json');
  die(json_encode(array('error' => $errtype)));
}


$action = empty($_POST['action']) ? 'front' : $_POST['action'];


if ($config->require_https) {
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    // HTTPS je zapnute, ide sa dalej
    header('Strict-Transport-Security: max-age=500');
  }
  else if ($action == 'front') {
    // sme na frontpage, redirectneme z http na https
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
    exit();
  }
  else {
    // sme na API callbacku, http je zakazane
    json_die('https required');
  }
}


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
