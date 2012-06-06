<?php

function import_cli() {
  import_action($_SERVER['argv'][2]);
}

function import_action($filename) {
  global $exam;
  global $dbh;
  $exam->importQuestions($filename);
  
  $sql = "CREATE VIEW subbody AS( SELECT q.qid, COUNT( * ) AS numberofsubquestions
                                FROM questions q, subquestions s
                                WHERE q.qid = s.qid
                                GROUP BY q.qid)";
  $sth = $dbh->prepare($sql);
  $sth->execute();
  
}
