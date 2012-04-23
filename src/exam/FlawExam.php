<?php

class FlawExam {
  public function getTitle() {
    return 'Prijímacia skúška';
  }

  public function getClientSoftTimeLimit() {
    return 60 * 60;
  }

  public function getClientHardTimeLimit() {
    return $this->getClientSoftTimeLimit() + 2 * 60;
  }

  public function getServerTimeLimit() {
    // extra time for the client JS to send the last batch of events
    return $this->getClientHardTimeLimit() + 30;
  }

  public function getUserQuestions($pid) {
    global $dbh;

    $sth = $dbh->prepare('
        SELECT uq.qorder AS qorder, q.body AS body
        FROM user_questions uq, questions q
        WHERE uq.pid = :pid AND uq.qid = q.qid');
    $sth->execute(array(':pid' => $pid));
    $questionBodies = $sth->fetchAll(PDO::FETCH_OBJ);

    $sth = $dbh->prepare('
        SELECT uq.qorder AS qorder, sq.qsubord as qsubord, sq.body AS body
        FROM user_questions uq, subquestions sq
        WHERE uq.pid = :pid AND uq.qid = sq.qid');
    $sth->execute(array(':pid' => $pid));
    $subquestionBodies = $sth->fetchAll(PDO::FETCH_OBJ);

    $assocresult = array();

    foreach ($questionBodies as $row) {
      $assocresult[$row->qorder] = array();
      $assocresult[$row->qorder]['body'] = $row->body;
    }
    foreach ($subquestionBodies as $row) {
      $assocresult[$row->qorder][$row->qsubord] = $row->body;
    }

    $numresult = array();
    for ($i = 0; $i < count($assocresult); $i++) {
      $numresult[$i] = $assocresult[$i];
    }

    return $numresult;
  }

  public function generateUserQuestions($pid) {
    // TODO buckets support

    global $dbh;

    $sth = $dbh->prepare('SELECT qid FROM questions');
    $sth->execute();
    $qids = $sth->fetchAll(PDO::FETCH_COLUMN);

    $sth = $dbh->prepare('INSERT INTO user_questions VALUES (:pid, :qorder, :qid)');

    $numQuestions = min(count($qids), 30);
    for ($i = 0; $i < $numQuestions; $i++) {
      do {
        $j = rand(0, count($qids)-1);
      } while ($qids[$j] === false);
      $qid = $qids[$j];
      $qids[$j] = false;
      $sth->execute(array(':pid' => $pid, ':qorder' => $i, ':qid' => $qid));
    }

    return $this->getUserQuestions($pid);
  }

  public function importQuestions($filename) {
    global $dbh;

    $dbh->beginTransaction();

    $doc = new DOMDocument();
    $doc->load($filename);
    $xpath = new DOMXPath($doc);

    $questions = array();

    $sth = $dbh->query('SELECT MAX(qid) + 1 FROM questions');
    $qid = $sth->fetchColumn();
    if ($qid === null) $qid = 0;

    $question_sth = $dbh->prepare('INSERT INTO questions VALUES (:qid, 0, :body)');
    $subquestion_sth = $dbh->prepare('INSERT INTO subquestions VALUES (:qid, :qsubord, :body, :value)');

    $xml_questions = $xpath->query('//question');
    if ($xml_questions) foreach ($xml_questions as $xml_question) {
      $id = $xml_question->getAttribute('id');   // TODO use it?
      $sample = $xml_question->getAttribute('sample') == 'true';   // TODO use it!
      $body = $xpath->query('body', $xml_question)->item(0)->firstChild->nodeValue;
      $question_sth->execute(array(':qid' => $qid, ':body' => $body));

      $xml_answers = $xpath->query('answer', $xml_question);
      $qsubord = 96;
      if ($xml_answers) foreach ($xml_answers as $xml_answer) {
        $qsubord++;
        $subquestion_sth->execute(array(
          ':qid' => $qid,
          ':qsubord' => chr($qsubord),
          ':body' => $xml_answer->firstChild->nodeValue,
          ':value' => $xml_answer->getAttribute('val'),
        ));
      }
      $qid++;
    }

    $dbh->commit();
  }

  public function getEventKeyFields() {
    return array(
      'qorder' => 'INTEGER NOT NULL',
      'qsubord' => 'CHAR NOT NULL',
    );
  }

  public function getEventValueFields() {
    return array(
      'value' => 'VARCHAR(255)',
      'time' => 'INTEGER NOT NULL',
    );
  }

  public function getUserExtraFields() {
    return array(
      'printed' => 'BOOLEAN NOT NULL DEFAULT FALSE',
    );
  }

  public function getExtraTables() {
    return array(
      'user_questions' => array(
        'pid' => 'VARCHAR(255) NOT NULL',
        'qorder' => 'INTEGER NOT NULL',
        'qid' => 'INTEGER NOT NULL',
      ),
      'questions' => array(
        'qid' => 'INTEGER NOT NULL',
        'bucket' => 'INTEGER NOT NULL',   // TODO or perhaps ENUM?
        'body' => 'TEXT NOT NULL',
      ),
      'subquestions' => array(
        'qid' => 'INTEGER NOT NULL',
        'qsubord' => 'CHAR NOT NULL',
        'body' => 'TEXT NOT NULL',
        'value' => 'VARCHAR(255) NOT NULL',
      ),
    );
  }
}
