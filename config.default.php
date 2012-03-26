<?php

$config = (object)array(
  // ci dovolujeme iba pripojenie cez HTTPS
  // (ak je TRUE, tak pouzivame aj Strict-Transport-Security header, takze
  // radsej zapinat iba ked je k dispozicii poriadny certifikat.)
  'require_https' => FALSE,

  // v demo mode sa prihlasuje umelym kodom a vysledky sa nikam neposielaju
  'demo_mode' => FALSE,

  // <title>
  'title' => 'Prijímacia skúška',
);
