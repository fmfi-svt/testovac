<?php

$config = (object)array(
  // v demo mode sa prihlasuje umelym kodom a vysledky sa nikam neposielaju
  'demo_mode' => FALSE,

  // ci sa ma zakazat refreshovanie testovaca klavesovymi skratkami
  'disable_refresh' => TRUE,

  // <title>
  'title' => 'Prijímacia skúška',

  // co sa vypise pod prihlasovacim formularom v demo rezime
  'demo_message' => 'Demo – použite ID 1234123412341234',
);


// nastavenie checksumu, ktorym sa overuje validnost UID
function config_validate_pid($pid) {
  global $config;
  if ($config->demo_mode) return ($pid === '1234123412341234');

  // example config: checksum je ze posledna cifra musi byt 7
  // (v ostrom configu sa odporuca mat nejaku lepsiu funkciu)
  return (preg_match('/^[1-9][0-9]{15}$/', $pid) && ($pid[15] == '7'));
}
