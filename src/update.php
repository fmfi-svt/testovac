<?php

// TODO check user id
// TODO check session id
// TODO check closed
// TODO check time
// TODO etc...

function save_jsonapi() {
  json_out(save_action(getarg('uid', 'string'), getarg('sessid', 'int'),
      getarg('saved', 'int'), getarg('events', 'array')));
}

function save_action($uid, $sessid, $savedBefore, $events) {
  return array('saved' => $savedBefore + count($events));
}

function close_jsonapi() {
  json_out(close_action(getarg('uid', 'string'), getarg('sessid', 'int')));
}

function close_action($uid, $sessid) {
  return (object)array();
}
