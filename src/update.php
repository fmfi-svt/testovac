<?php

function _save_get_user($pid, $sessid) {
  global $dbh, $pid_checker;
  if (!$pid_checker->check($pid)) return 'invalid pid';

  $sth = $dbh->prepare('SELECT * FROM users WHERE pid = :pid');
  $sth->execute(array(':pid' => $pid));
  $user = $sth->fetchObject();
  if ($user === false) return 'invalid pid';

  if ($user->sessid != $sessid) return 'invalid sessid';

  return $user;
}

function save_jsonapi() {
  json_out(save_action(getarg('pid', 'string'), getarg('sessid', 'int'),
      getarg('savedEvents', 'int'), getarg('events', 'array')));
}

function save_action($pid, $sessid, $clientSavedEvents, $events) {
  global $dbh, $exam;

  $dbh->beginTransaction();

  $user = _save_get_user($pid, $sessid);
  if (is_string($user)) return array('error' => $user);

  if (user_closed($user)) return array('error' => 'closed');

  $sth = $dbh->prepare('SELECT COUNT(*) FROM events WHERE pid = :pid');
  $sth->execute(array(':pid' => $pid));
  $serverSavedEvents = $sth->fetchColumn();

  // serverSavedEvents = how many events are in the server database.
  // clientSavedEvents = how many events the client believes we have.
  // that might be less than serverSavedEvents if the client's info is out of
  // date (we ignore the duplicates in that case), but it cannot be more.
  if ($clientSavedEvents > $serverSavedEvents) {
    return array('error' => 'invalid savedEvents');
  }

  $event_fields = array_merge(
    array_keys($exam->getEventKeyFields()),
    array_keys($exam->getEventValueFields()));
  $query = build_query('
    INSERT INTO events (pid, serial, {keys|%|,})
    VALUES (:pid, :serial, {keys|:%|,})',
    array('keys' => $event_fields));
  $sth = $dbh->prepare($query);

  for ($i = 0; $i < count($events); $i++) {
    foreach ($event_fields as $field) {
      if (!isset($events[$i][$field]) || !is_string($events[$i][$field])) {
        return array('error' => 'bad request');
      }
    }
  }

  $newSavedEvents = $serverSavedEvents;

  for ($i = 0; $i < count($events); $i++) {
    $serial = $clientSavedEvents + $i;
    // if the server already has this event, ignore it
    if ($serial < $serverSavedEvents) continue;

    $parameters = array(':pid' => $pid, ':serial' => $serial);
    foreach ($event_fields as $field) {
      $parameters[':'.$field] = $events[$i][$field];
    }
    $sth->execute($parameters);
    $newSavedEvents++;
  }

  $dbh->commit();

  return array('savedEvents' => $newSavedEvents, 'beginTime' => (int)$user->begintime);
}

function close_jsonapi() {
  json_out(close_action(getarg('pid', 'string'), getarg('sessid', 'int')));
}

function close_action($pid, $sessid) {
  global $dbh;

  $dbh->beginTransaction();

  $user = _save_get_user($pid, $sessid);
  if (is_string($user)) return array('error' => $user);

  $sth = $dbh->prepare('UPDATE users SET submitted = TRUE WHERE pid = :pid');
  $sth->execute(array(':pid' => $pid));

  $dbh->commit();

  return (object)array();
}
