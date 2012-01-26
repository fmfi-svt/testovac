<?php

$questions = array();
for ($i = 0; $i < 30; $i++) {
  $question = array('Zadanie otázky číslo '.$i);
  for ($j = 0; $j < 4; $j++)
    $question[] = 'Možnosť '.$j.' otázky '.$i.' (ktorá je '.(rand()%2 ? 'správna' : 'nesprávna').')';
  $questions[] = $question;
}

json_out(array(
  'session' => rand(),
  'beginTime' => time(),
  'questions' => array($questions),
  'state' => array(),
  'serialOffset' => 0,
));
