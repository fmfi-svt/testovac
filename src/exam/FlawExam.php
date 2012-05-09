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
    global $dbh;

    $sth = $dbh->prepare('SELECT bid, size FROM buckets ORDER BY border ASC');
    $sth->execute();
    $buckets = $sth->fetchAll(PDO::FETCH_OBJ);

    $sth = $dbh->prepare('SELECT bid, qid FROM questions');
    $sth->execute();
    $qids = $sth->fetchAll(PDO::FETCH_OBJ);
    $qidsByBucket = array();
    foreach ($qids as $row) {
      $qidsByBucket[$row->bid][] = $row->qid;
    }

    $qorder = 0;
    $sth = $dbh->prepare('INSERT INTO user_questions VALUES (:pid, :qorder, :qid)');

    foreach ($buckets as $row) {
      $qidsHere = $qidsByBucket[$row->bid];
      $size = min(count($qidsHere), $row->size);
      for ($i = 0; $i < $size; $i++) {
        do {
          $j = rand(0, count($qidsHere)-1);
        } while ($qidsHere[$j] === false);
        $qid = $qidsHere[$j];
        $qidsHere[$j] = false;
        $sth->execute(array(':pid' => $pid, ':qorder' => $qorder, ':qid' => $qid));
        $qorder++;
      }
    }

    return $this->getUserQuestions($pid);
  }

  public function importQuestions($filename) {
    global $dbh;

    $dbh->beginTransaction();

    $doc = new DOMDocument();
    $doc->load($filename);
    $xpath = new DOMXPath($doc);

    $sth = $dbh->query('SELECT MAX(bid) + 1 FROM buckets');
    $bid = $sth->fetchColumn();
    if ($bid === null) $bid = 0;

    $sth = $dbh->query('SELECT MAX(qid) + 1 FROM questions');
    $qid = $sth->fetchColumn();
    if ($qid === null) $qid = 0;

    $bucket_sth = $dbh->prepare('INSERT INTO buckets VALUES (:bid, :border, :size, :points)');
    $question_sth = $dbh->prepare('INSERT INTO questions VALUES (:qid, :bid, :body)');
    $subquestion_sth = $dbh->prepare('INSERT INTO subquestions VALUES (:qid, :qsubord, :body, :value)');

    $xml_buckets = $xpath->query('//library');
    if ($xml_buckets) foreach ($xml_buckets as $xml_bucket) {
      $order = $xml_bucket->getAttribute('order');
      $size = $xml_bucket->getAttribute('size');
      $points = $xml_bucket->getAttribute('points');
      $bucket_sth->execute(array(':bid' => $bid, ':border' => $order, ':size' => $size, ':points' => $points));

      $xml_questions = $xpath->query('question', $xml_bucket);
      if ($xml_questions) foreach ($xml_questions as $xml_question) {
        $body = $xpath->query('body', $xml_question)->item(0)->firstChild->nodeValue;
        $question_sth->execute(array(':qid' => $qid, ':bid' => $bid, ':body' => $body));

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
      $bid++;
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
      'buckets' => array(
        'bid' => 'INTEGER NOT NULL',
        'border' => 'INTEGER NOT NULL',
        'size' => 'INTEGER NOT NULL',
        'points' => 'INTEGER NOT NULL',
      ),
      'user_questions' => array(
        'pid' => 'VARCHAR(255) NOT NULL',
        'qorder' => 'INTEGER NOT NULL',
        'qid' => 'INTEGER NOT NULL',
      ),
      'questions' => array(
        'qid' => 'INTEGER NOT NULL',
        'bid' => 'INTEGER NOT NULL',
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
