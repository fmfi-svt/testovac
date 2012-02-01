<?php

function login_jsonapi() {
  json_out(login_action(getarg('uid', 'string')));
}

function login_action($uid) {
  if (!config_validate_uid($uid)) return array('error' => 'invalid uid');

  $questions = array();
  for ($i = 1; $i <= 30; $i++) {
    $question = array('Zadanie otázky číslo '.$i);
    for ($j = 1; $j <= 4; $j++)
      $question[] = 'Možnosť '.$j.' otázky '.$i.' (správne je '.(rand()%2 ? 'áno' : 'nie').')';
    $questions[] = $question;
  }

  return array(
    'sessid' => rand(),
    'beginTime' => time(),
    'questions' => $questions,
    'state' => (object)array(),
    'serverSerial' => 0,
  );
}
