<?php

use PDO;

require_once __DIR__ . '/../config.php';

class Repository {

    /** @var PDO $db */
    private $db;

    function __construct() {
        $this->db = connect_db();
    }

    function getAllStudents() {
        $query = $this->db->query('SELECT s1.*, s2.id as duplicate FROM Students as s1
left join Students as s2 on
s1.meno = s2.meno and s1.priezvisko=s2.priezvisko and s1.datum_narodenia=s2.datum_narodenia and
not(s1.forma_studia = s2.forma_studia) ORDER BY sign(s1.pid), s1.priezvisko');
        return $query;
    }

    function getStudentsForAverage() {
        $query = $this->db->query('SELECT s1.*, s2.id as duplicate FROM Students as s1
left join Students as s2 on
s1.meno = s2.meno and s1.priezvisko=s2.priezvisko and s1.datum_narodenia=s2.datum_narodenia and
not(s1.forma_studia = s2.forma_studia) ORDER BY sign(s1.pid), s1.priezvisko');
        return $query;
    }

    function printStudents() {
        $query = $this->db->query('SELECT * from Students WHERE printed = 0 AND pid is not null ORDER BY pid');

        $students = $query->fetchAll(PDO::FETCH_ASSOC);
        $logmsg = 'PRINT; ';
        foreach ($students as $row) {
            $pid = $row['pid'];
            $logmsg = $logmsg . $pid . ' ';
        }

        $logmsg = $logmsg . " , edit_by:admin , time:" . date('G-i-s+j/m/y');

        $this->writeToLog($logmsg);
        $this->db->exec('UPDATE Students SET printed = 1 WHERE printed = 0 AND pid is not null');
        return $students;
    }

    function exportStudents() {
        $query = $this->db->query('SELECT * from Students WHERE printed = 1 ORDER BY pid');
        $this->db->exec('UPDATE Students SET exported = 1 WHERE exported = 0 AND printed = 1');
        $logmsg = 'EXPORT; ';

        $logmsg = $logmsg . " edit_by:admin , time:" . date('G-i-s+j/m/y');

        $this->writeToLog($logmsg);
        return $query;
    }

    function updateStudents() {
        $id = $_POST['id'];
        if ($id == -1) {
            return;
        }
        $logMessage = "UPDATE; ID:" . $id;
        print_r($_POST);
        if ($_POST['delete'] == 'yes') {
            $sth = $this->db->prepare("UPDATE Students SET pid = NULL WHERE id=:id");
            $sth->bindParam(':id', $id);
            $sth->execute();

            $logMessage = $logMessage . " , ID: " . $id . "  !!! POZOR VYMAZANY PID !!!";
        }

        if (isset($_POST['pid'])) {
            if ($_POST['pid'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET pid = :pid WHERE id=:id");
                $pid = $_POST['pid'];
                $sth->bindParam(':id', $id);
                $sth->bindParam(':pid', $pid);
                $sth->execute();
                
                $sth2 = $this->db->prepare("UPDATE Students SET time_of_registration = NOW() WHERE id=:id");
                $sth2->bindParam(':id', $id);
                $sth2->execute();

                $logMessage = $logMessage . " , PID:" . $pid;
            }
        }
        if (isset($_POST['priemer1'])) {
            if ($_POST['priemer1'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET priemer1 = :priemer1 WHERE id=:id");
                $priemer1 = $_POST['priemer1'];
                $priemer1_bodka = str_replace(',', '.', $priemer1);
                $sth->bindParam(':priemer1', $priemer1_bodka);
                $sth->bindParam(':id', $id);
                $sth->execute();
                $logMessage = $logMessage . " , priemer1:" . $priemer1;
            } else {
                $sth = $this->db->prepare("UPDATE Students SET priemer1 = NULL WHERE id=:id");
                $sth->bindParam(':id', $id);
                $sth->execute();
                $logMessage = $logMessage . "vymazany priemer1 pre studenta , ID:" . $id;
            }
        }

        if (isset($_POST['priemer2'])) {
            if ($_POST['priemer2'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET priemer2 = :priemer2 WHERE id=:id");
                $priemer2 = $_POST['priemer2'];
                $priemer2_bodka = str_replace(',', '.', $priemer2);
                $sth->bindParam(':priemer2', $priemer2_bodka);
                $sth->bindParam(':id', $id);
                $sth->execute();
                $logMessage = $logMessage . " , priemer2:" . $priemer2;
            } else {
                $sth = $this->db->prepare("UPDATE Students SET priemer2 = NULL WHERE id=:id");
                $sth->bindParam(':id', $id);
                $sth->execute();
                $logMessage = $logMessage . "vymazany priemer2 pre studenta , ID:" . $id;
            }
        }
        $logMessage = $logMessage . " , edit_by:" . $_SERVER['REMOTE_USER'] . " , time:" . date('G-i-s+j/m/y');

        if (isset($_POST['info'])) {
            $studentname = $_POST['info'];
        }
        $_SESSION['sprava'] = "Študent $studentname úspešne uložený.";
        $_SESSION['counter'] = 2;
        $this->writeToLog($logMessage);
    }

    function writeToLog($msg) {
        //echo $msg;
        $msg = $msg . " \n";
        $fileloc = "logs/db_log.log";
        $handle = fopen($fileloc, 'a');
        fputs($handle, $msg);
        fclose($handle);
    }

    function sqlDateToRegular($date) {
        $parts = preg_split("/[-]+/", $date);
        $date = $parts[2] . '.' . $parts[1] . '.' . $parts[0];
        return $date;
    }

    function regularDateToSql($date) {
        $parts = preg_split("/[.]+/", $date);
        $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        return $date;
    }

}

?>
