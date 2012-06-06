<?php

class FlawExam {

    public function getTitle() {
        return 'Prijímacia skúška';
    }

    public function getClientTimeLimit() {
        return 60 * 60;
    }

    public function getServerTimeLimit() {
        // extra time for the client JS to send the last batch of events
        return $this->getClientTimeLimit() + 30;
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
        SELECT uq.qorder AS qorder, sq.qsubord as qsubord, sq.body AS body,
        (sq.value = "true" OR sq.value = "false") AS isbool
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
            $assocresult[$row->qorder][$row->qsubord] = array('body' => $row->body, 'type' => ($row->isbool ? 'bool' : 'text'));
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
                    $j = rand(0, count($qidsHere) - 1);
                } while ($qidsHere[$j] === false);
                $qid = $qidsHere[$j];
                $qidsHere[$j] = false;
                $sth->execute(array(':pid' => $pid, ':qorder' => $qorder, ':qid' => $qid));
                $qorder++;
            }
        }

        return $this->getUserQuestions($pid);
    }

    private function loadBody($doc, $body) {
        $result = array();
        foreach ($body->childNodes as $child)
            $result[] = $doc->saveXML($child);
        return implode('', $result);
    }

    public function importQuestions($filename) {
        global $dbh;

        $dbh->beginTransaction();

        $doc = new DOMDocument();
        $doc->load($filename);
        $xpath = new DOMXPath($doc);

        $sth = $dbh->query('SELECT MAX(bid) + 1 FROM buckets');
        $bid = $sth->fetchColumn();
        if ($bid === null)
            $bid = 0;

        $sth = $dbh->query('SELECT MAX(qid) + 1 FROM questions');
        $qid = $sth->fetchColumn();
        if ($qid === null)
            $qid = 0;

        $bucket_sth = $dbh->prepare('INSERT INTO buckets VALUES (:bid, :border, :size, :points)');
        $question_sth = $dbh->prepare('INSERT INTO questions VALUES (:qid, :bid, :body)');
        $subquestion_sth = $dbh->prepare('INSERT INTO subquestions VALUES (:qid, :qsubord, :body, :value)');

        $xml_buckets = $xpath->query('//library');
        if ($xml_buckets)
            foreach ($xml_buckets as $xml_bucket) {
                $order = $xml_bucket->getAttribute('order');
                $size = $xml_bucket->getAttribute('size');
                $points = $xml_bucket->getAttribute('points');
                $bucket_sth->execute(array(':bid' => $bid, ':border' => $order, ':size' => $size, ':points' => $points));

                $xml_questions = $xpath->query('question', $xml_bucket);
                if ($xml_questions)
                    foreach ($xml_questions as $xml_question) {
                        $body = $this->loadBody($doc, $xpath->query('body', $xml_question)->item(0));
                        $question_sth->execute(array(':qid' => $qid, ':bid' => $bid, ':body' => $body));

                        $xml_answers = $xpath->query('answer', $xml_question);
                        $qsubord = 96;
                        if ($xml_answers)
                            foreach ($xml_answers as $xml_answer) {
                                $qsubord++;
                                $subquestion_sth->execute(array(
                                    ':qid' => $qid,
                                    ':qsubord' => chr($qsubord),
                                    ':body' => $this->loadBody($doc, $xml_answer),
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

    public function getUsers() {
        global $dbh;

        $sth = $dbh->prepare('SELECT * FROM users');
        $sth->execute();
        $users = $sth->fetchAll(PDO::FETCH_OBJ);

        return $users;
    }

    public function getFinishedUsers() {
        $users = $this->getUsers();
        $finishedUsers = array();
        foreach ($users as $user) {
            if (user_closed($user)) {
                array_push($finishedUsers, $user);
            }
        }

        return $finishedUsers;
    }

    public function getFinishedUsersForPrinting() {
        $finishedUsers = $this->getFinishedUsers();
        $finishedUsersForPrinting = array();
        foreach ($finishedUsers as $finishedUser) {
            if (!user_printed($finishedUser)) {
                array_push($finishedUsersForPrinting, $finishedUser);
            }
        }

        return $finishedUsersForPrinting;
    }

    public function userPrinted($pid) {
        global $dbh;

        $sth = $dbh->prepare("UPDATE users SET printed=1 WHERE pid=:pid");
        $sth->execute(array(':pid' => $pid));

        return $sth->rowCount();
    }

    public function getUserAnswers($pid) {
        global $dbh;
        
        $sql = "select uq.qorder,sq.qid,sq.qsubord,sq.value as correctanswer,e.value as useranswer,b.points, sb.numberofsubquestions as nsq,q.body as questionbody,sq.body as subquestionbody 
from (user_questions as uq
     join subquestions as sq 
     on uq.qid = sq.qid)
     left join events as e
        on uq.pid = e.pid and uq.qorder = e.qorder and 
           sq.qsubord = e.qsubord
     join questions as q on uq.qid = q.qid
     join subbody as sb on sb.qid = uq.qid
     join buckets as b on q.bid = b.bid
where uq.pid = :pid
order by uq.qorder,sq.qsubord;
";
//        $sth->execute(array(':pid' => $pid));
//        print_r($sth);
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute(array(':pid' => $pid));
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
        $userAnswers = $sth->fetchAll(PDO::FETCH_OBJ);

        return $userAnswers;
    }

    public function getUserPoints($pid) {
        $userAnswers = $this->getUserAnswers($pid);
        $userPoints = 0;
        foreach ($userAnswers as $userAnswer) {
            if ($userAnswer->useranswer === $userAnswer->correctanswer) {
                $userPoints += $userAnswer->points;
            }
        }
        return $userPoints;
    }

    public function getSubAnswerUser($userAnswers, $qorder, $qsubord) {
        //$userAnswers = $this->getUserAnswers($pid);
        foreach ($userAnswers as $userAnswer) {
            if (($userAnswer->qorder == $qorder) && ($userAnswer->qsubord == $qsubord) && ($userAnswer->useranswer != '')) {
                if ($userAnswer->useranswer == 'true') {
                    return 'Áno.';
                }
                if ($userAnswer->useranswer == 'false') {
                    return 'Nie.';
                }
                return $userAnswer->useranswer; 
            }
        }
        return '';
    }
    
    public function getCompleteSubAnswerUser($userAnswers, $qorder, $qsubord) {
        //$userAnswers = $this->getUserAnswers($pid);
        $result = array();
        foreach ($userAnswers as $userAnswer) {
            if (($userAnswer->qorder == $qorder) && ($userAnswer->qsubord == $qsubord)) {
                if ($userAnswer->correctanswer == 'true') {
                    $result['correctanswer'] = 'Áno.';
                }else
                if ($userAnswer->correctanswer == 'false') {
                    $result['correctanswer'] = 'Nie.';
                } else {
                     $result['correctanswer'] = $userAnswer->correctanswer;
                }
                    $result['points'] = $userAnswer->points; 
                    $result['nsq'] = $userAnswer->nsq; 
            }
        }
        return $result;
    }

}
