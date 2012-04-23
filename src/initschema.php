<?php

function initschema_cli() {
  initschema_action();
}

function initschema_action() {
  global $dbh, $exam;

  $schema['users'] = array(
    'pid' => 'VARCHAR(255) NOT NULL PRIMARY KEY',
    'sessid' => 'INTEGER NOT NULL',
    'begintime' => 'INTEGER NOT NULL',
    'submitted' => 'BOOLEAN NOT NULL DEFAULT FALSE',
  );
  $schema['users'] += $exam->getUserExtraFields();

  $schema['events'] = array(
    'pid' => 'VARCHAR(255) NOT NULL',
    'serial' => 'INTEGER NOT NULL',
  );
  $schema['events'] += $exam->getEventKeyFields();
  $schema['events'] += $exam->getEventValueFields();

  $schema += $exam->getExtraTables();

  foreach ($schema as $table_name => $fields) {
    $columns = array();
    foreach ($fields as $name => $type) $columns[] = $name . ' ' . str_replace('BOOLEAN','INTEGER',$type);
    $dbh->query('CREATE TABLE ' . $table_name .
        ' (' . implode(',', $columns) . ')');
  }
}
