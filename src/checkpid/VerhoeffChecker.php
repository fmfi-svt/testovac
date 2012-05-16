<?php

class VerhoeffChecker {
  private static $D = array(
    array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
    array(1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
    array(2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
    array(3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
    array(4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
    array(5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
    array(6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
    array(7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
    array(8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
    array(9, 8, 7, 6, 5, 4, 3, 2, 1, 0),
  );

  private static $P = array(
    array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
    array(1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
    array(5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
    array(8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
    array(9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
    array(4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
    array(2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
    array(7, 0, 4, 6, 9, 1, 3, 2, 5, 8),
  );

  private static $INV = array(0, 4, 3, 2, 1, 5, 6, 7, 8, 9);

  private function computeChecksum($digits) {
    $c = 0;
    $i = 0;
    foreach (array_reverse($digits) as $digit) {
      $c = self::$D[$c][self::$P[$i%8][$digit]];
      $i++;
    }
    return $c;
  }

  public function check($pid) {
    if (!preg_match('/^[0-9]{4}(?:-[0-9]{4}){3}$/', $pid)) return false;
    $digits = str_split(str_replace('-', '', $pid));
    return $this->computeChecksum($digits) === 0;
  }

  public function generatePid($demo = false) {
    $digits = array();
    for ($i = 0; $i < 16; $i++) $digits[] = rand(0, 9);
    if ($demo) {
      // demo: tretia a osma cifra maju sucet 8
      $digits[2] = rand(0, 8);
      $digits[7] = 8 - $digits[2];
    }
    else {
      // rocnik 2012: tretia a osma cifra maju sucet 12
      $digits[2] = rand(3, 9);
      $digits[7] = 12 - $digits[2];
    }
    $digits[15] = 0;
    $checksum = $this->computeChecksum($digits);
    $digits[15] = self::$INV[$checksum];
    $str = implode('', $digits);
    return substr($str, 0, 4).'-'.substr($str, 4, 4).'-'.substr($str, 8, 4).'-'.substr($str, 12, 4);
  }
}
