<?php

function givetime_cli() {
  $minutes = $_SERVER['argv'][3];
  if ((string)((int)$minutes) !== ((string)$minutes)) {
    $result = array('error' => 'not an integer');
  }
  else {
    $result = givetime_action($_SERVER['argv'][2], $_SERVER['argv'][3]);
  }
  if (isset($result['error'])) {
    print $result['error']."\n";
    exit(1);
  }
}

function givetime_action($pid, $minutes) {
  global $dbh;

  $dbh->beginTransaction();

  $sth = $dbh->prepare('SELECT * FROM users WHERE pid = :pid');
  $sth->execute(array(':pid' => $pid));
  $user = $sth->fetchObject();
  if ($user === false) return array('error' => 'invalid pid');

  $sth = $dbh->prepare('UPDATE users SET begintime = begintime + :time WHERE pid = :pid');
  $sth->execute(array(':pid' => $pid, ':time' => $minutes * 60));

  $dbh->commit();

  return array();
}
