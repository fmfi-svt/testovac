<?php

// TODO check user id
// TODO check session id
// TODO check closed
// TODO check time
// TODO etc...

function save_jsonapi() {
  json_out(save_action(getarg('pid', 'string'), getarg('sessid', 'int'),
      getarg('saved', 'int'), getarg('events', 'array')));
}

function save_action($pid, $sessid, $savedBefore, $events) {
  return array('saved' => $savedBefore + count($events));
}

function close_jsonapi() {
  json_out(close_action(getarg('pid', 'string'), getarg('sessid', 'int')));
}

function close_action($pid, $sessid) {
  return (object)array();
}
