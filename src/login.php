<?php

function login_jsonapi() {
  json_out(login_action(getarg('pid', 'string')));
}

function _login_get_state($pid) {
  global $dbh, $exam;

  $fields = array(
    'kf' => array_keys($exam->getEventKeyFields()),
    'vf' => array_keys($exam->getEventValueFields()),
  );
  $query = build_query('
      SELECT {kf|e.% AS %|,}, {vf|e.% AS %|,} FROM events e
      JOIN (SELECT {kf|%|,}, MAX(serial) AS max_serial FROM events
          WHERE pid = :pid GROUP BY {kf|%|,}) x
      WHERE e.pid = :pid AND e.serial = x.max_serial
      AND {kf|e.% = x.%| AND }', $fields);
  $sth = $dbh->prepare($query);
  $sth->execute(array(':pid' => $pid));
  return $sth->fetchAll(PDO::FETCH_OBJ);
}

function login_action($pid) {
  global $dbh, $exam, $pid_checker;
  if (!$pid_checker->check($pid)) return array('error' => 'invalid pid');

  $dbh->beginTransaction();

  $newSessid = rand();   // TODO better randomness source?

  $sth = $dbh->prepare('SELECT * FROM users WHERE pid = :pid');
  $sth->execute(array(':pid' => $pid));
  $user = $sth->fetchObject();

  if ($user !== false) {
    if (user_closed($user)) return array('error' => 'closed');

    $sth = $dbh->prepare('UPDATE users SET sessid = :sessid WHERE pid = :pid;');
    $sth->execute(array(':pid' => $pid, ':sessid' => $newSessid));

    $beginTime = $user->begintime;

    $questions = $exam->getUserQuestions($pid);

    $state = _login_get_state($pid);

    $sth = $dbh->prepare('SELECT COUNT(*) FROM events WHERE pid = :pid');
    $sth->execute(array(':pid' => $pid));
    $savedEvents = $sth->fetchColumn();
  }
  else {
    $beginTime = time();
    $sth = $dbh->prepare('INSERT INTO users (pid, sessid, begintime) VALUES (:pid, :sessid, :begintime)');
    $sth->execute(array(':pid' => $pid, ':sessid' => $newSessid, ':begintime' => $beginTime));

    $questions = $exam->generateUserQuestions($pid);

    $state = array();

    $savedEvents = 0;
  }

  $dbh->commit();

  return array(
    'sessid' => (int)$newSessid,
    'beginTime' => (int)$beginTime,
    'now' => microtime(true),
    'questions' => $questions,
    'state' => $state,
    'savedEvents' => (int)$savedEvents,
  );
}
