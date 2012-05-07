<?php

$questions = array();
for ($i = 1; $i <= 30; $i++) {
  $question = array('Zadanie otázky číslo '.$i);
  for ($j = 1; $j <= 4; $j++)
    $question[] = 'Možnosť '.$j.' otázky '.$i.' (správne je '.(rand()%2 ? 'áno' : 'nie').')';
  $questions[] = $question;
}

json_out(array(
  'session' => rand(),
  'beginTime' => time(),
  'questions' => $questions,
  'state' => (object)array(),
  'serverSerial' => 0,
));
