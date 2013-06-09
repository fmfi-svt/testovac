<?php

require_once __DIR__ . '/../config.php';

class Repository {

    /** @var PDO $db */
    private $db;

    function __construct() {
        $this->db = connect_db();
    }

    function getAllStudents() {
        $query1 = $this->db->query('SET @rank := 0;');
        $query2 = $this->db->query('
            SELECT * FROM  
            (SELECT *,@rank := @rank+1 AS rank 
            FROM Students 
            ORDER BY priezvisko,meno) as LOL
            ORDER BY sign(pid),priezvisko;');
        return $query2;
    }

    function getStudentsForAverage() {
        $query = $this->db->query('
            SELECT *
            FROM Students 
            ORDER BY priezvisko,meno;');
        return $query;
    }

    function updateStudents() {
        $id = $_POST['id'];
        if ($id == -1) {
            return;
        }
        $logMessage = "UPDATE; ID:" . $id;
        if ($_POST['delete'] == 'yes') {
            $sth = $this->db->prepare("UPDATE Students SET pid = NULL, time_of_registration = NULL WHERE id=:id");
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
        $logMessage = $logMessage . " , edit_by:" . $_SESSION['user'] . " , time:" . date('G-i-s+j/m/y');

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
