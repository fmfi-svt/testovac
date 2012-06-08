<?php

class DemoChecker {
  public function check($pid) {
    // priklad checkera: podmienka je ze posledna cifra musi byt 7
    // (v ostrom configu sa odporuca mat nejaku lepsiu funkciu)
    return (preg_match('/^[1-9][0-9]{15}$/', $pid) && ($pid[15] == '7'));
  }

  public function generatePid($demo = false) {
    return '4567456745674567';
  }
}
