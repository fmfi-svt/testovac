<?php

function _schema_get() {
  global $exam;

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

  return $schema;
}

function initschema_cli() {
  initschema_action();
}

function initschema_action() {
  global $dbh;
  $schema = _schema_get();

  foreach ($schema as $table_name => $fields) {
    $columns = array();
    foreach ($fields as $name => $type) $columns[] = $name . ' ' . str_replace('BOOLEAN','INTEGER',$type);
    $dbh->query('CREATE TABLE ' . $table_name .
        ' (' . implode(',', $columns) . ')');
  }
  initindexes();
}

function initindexes() {
    global $dbh;
    $dbh->query('CREATE INDEX bid_index ON buckets (bid)');
    $dbh->query('CREATE INDEX pid_index ON events (pid)');
    $dbh->query('CREATE INDEX questions_index ON questions (qid)');
    $dbh->query('CREATE INDEX subquestions_index ON subquestions (qid)');
    $dbh->query('CREATE INDEX users_index ON users (pid)');
    $dbh->query('CREATE INDEX usersquestions_index ON user_questions (pid)');
    $dbh->query('CREATE INDEX questionsusers_index ON user_questions (qid)');
}

function droptables_cli() {
  droptables_action();
}

function droptables_action() {
  global $dbh;
  $schema = _schema_get();
  $sql = "DROP VIEW IF EXISTS subbody";
  $sth = $dbh->prepare($sql);
  $sth->execute();

  foreach ($schema as $table_name => $fields) {
    $dbh->query('DROP TABLE IF EXISTS ' . $table_name);
  }
}
