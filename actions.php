<?php

require_once 'config.php';

class Repository {

    private $db;

    function __construct() {
        $this->db = connect_db();
        if (PEAR::isError($this->db)) {
            die($this->db->getMessage());
        }
        $this->db->setFetchMode(DB_FETCHMODE_ASSOC);
    }

    function getAllStudents() {
        $this->db->query('SET NAMES UTF8;');
        //$query = & $this->db->query('SELECT * from Students ORDER BY sign(pid), priezvisko');
        $query = & $this->db->query('SELECT s1.*, s2.id as duplicate FROM Students as s1
left join Students as s2 on
s1.meno = s2.meno and s1.priezvisko=s2.priezvisko and s1.datum_narodenia=s2.datum_narodenia and
not(s1.forma_studia = s2.forma_studia) ORDER BY sign(s1.pid), s1.priezvisko');
        // Always check that result is not an error
        if (PEAR::isError($query)) {
            die($query->getMessage());
        }
        return $query;
    }

    function printStudents() {
        print_r('asdasd');
        $this->db->query('SET NAMES UTF8;');
        $query = & $this->db->query('SELECT * from Students WHERE printed = 0 AND pid is not null ORDER BY pid');
        $retquery = & $this->db->query('SELECT * from Students WHERE printed = 0 AND pid is not null ORDER BY pid');
        $printquery = & $this->db->query('UPDATE Students SET printed = 1 WHERE printed = 0 AND pid is not null');
        // Always check that result is not an error
        if (PEAR::isError($query)) {
            die($query->getMessage());
        }
        if (PEAR::isError($printquery)) {
            die($printquery->getMessage());
        }

        $logmsg = 'PRINT; ';
        while ($query->fetchInto($row)) {
            $pid = $row['pid'];
            $logmsg = $logmsg . $pid . ' ';
        }

        $logmsg = $logmsg . " , edit_by:admin , time:" . date('G-i-s+j/m/y');

        $this->writeToLog($logmsg);
        return $retquery;
    }

    function exportStudents() {
        $this->db->query('SET NAMES UTF8;');
        $query = & $this->db->query('SELECT * from Students WHERE printed = 1 ORDER BY pid');
        $exportquery = $this->db->query('UPDATE Students SET exported = 1 WHERE exported = 1 AND printed = 0');
        // Always check that result is not an error
        if (PEAR::isError($query)) {
            die($query->getMessage());
        }
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
            $sth = $this->db->prepare("UPDATE Students SET pid = NULL WHERE id=" . $id);
            $this->db->execute($sth);

            $logMessage = $logMessage . " , PID:" . $data . "  !!! POZOR VYMAZANY PID !!!";
        }

        if (isset($_POST['pid'])) {
            if ($_POST['pid'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET pid = (?) WHERE id=" . $id);
                $data = $_POST['pid'];
                $this->db->execute($sth, $data);

                $logMessage = $logMessage . " , PID:" . $data;
            }
        }
        if (isset($_POST['priemer1'])) {
            if ($_POST['priemer1'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET priemer1 = (?) WHERE id=" . $id);
                $data = $_POST['priemer1'];
                $this->db->execute($sth, str_replace(',', '.', $data));
                $logMessage = $logMessage . " , priemer1:" . $data;
            }
        }
        if (isset($_POST['priemer2'])) {
            if ($_POST['priemer2'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET priemer2 = (?) WHERE id=" . $id);
                $data = $_POST['priemer2'];
                $this->db->execute($sth, str_replace(',', '.', $data));
                $logMessage = $logMessage . " , priemer2:" . $data;
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
