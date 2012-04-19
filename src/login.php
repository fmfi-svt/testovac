<?php

function login_jsonapi() {
  json_out(login_action(getarg('pid', 'string')));
}

function login_action($pid) {
  global $pid_checker;
  if (!$pid_checker->check($pid)) return array('error' => 'invalid pid');

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
