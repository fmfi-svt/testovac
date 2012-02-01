<?php

function json_out($data) {
  header('Content-Type: application/json; charset=UTF-8');
  print json_encode($data);
  exit();
}

function getarg($var, $type) {
  // pomocna funkcia na zistovanie $_POST inputu so zakladnou validaciou
  // lebo v PHP "foo[]=a&foo[]=b" sposobi ze $_POST['foo'] je pole,
  // co vacsinou neni to co ten kod ocakava :(
  if (isset($_POST[$var])) {
    if ($type === 'string' && is_string($_POST[$var])) {
      return $_POST[$var];
    }
    if ($type === 'int' && preg_match('/^(0|[1-9][0-9]*)$/', $_POST[$var])) {
      return $_POST[$var] + 0;
    }
    if ($type === 'number' && is_numeric($_POST[$var])) {
      return $_POST[$var] + 0;
    }
    if ($type === 'array' && is_array($_POST[$var])) {
      return $_POST[$var];
    }
    if ($type === 'anything') {
      return $_POST[$var];
    }
  }
  json_out(array('error' => 'bad request'));
}
