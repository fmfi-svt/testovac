<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logger.php';

class Repository {

    /** @var PDO $db */
    private $db;
    private $logger;

    function __construct() {
        $this->db = connect_db();
        $this->logger = new Logger($this->db);
    }

    function getAllStudents() {
        $this->logger->writeToLog('lol');
        $query1 = $this->db->query('SET @rank := 0;');
        $query2 = $this->db->query('
            SELECT * FROM  
            (SELECT *,@rank := @rank+1 AS rank 
            FROM Students 
            ORDER BY priezvisko,meno) as LOL
            ORDER BY case when pid is NULL then 0 else 1 end,priezvisko,meno;');
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
        $info = $_POST['info'];
        $user = $_SESSION['user'];

        if ($_POST['delete'] == 'yes') {
            $sth = $this->db->prepare("UPDATE Students SET pid = NULL, time_of_registration = NULL WHERE id=:id");
            $sth->bindParam(':id', $id);
            $sth->execute();
            $this->logger->writeToLog('delete', 'pid', $id, $info, $user);
        }
        
        if (isset($_POST['pid'])) {
            $pid = $_POST['pid'];
            $sth = $this->db->prepare("UPDATE Students SET pid = :pid WHERE id=:id");
            $sth->bindParam(':id', $id);
            $sth->bindParam(':pid', $pid);
            $sth->execute();

            $sth2 = $this->db->prepare("UPDATE Students SET time_of_registration = NOW() WHERE id=:id");
            $sth2->bindParam(':id', $id);
            $sth2->execute();

            $this->logger->writeToLog('update', 'pid', $id, $info, $user,$pid);
        }
        
        if (isset($_POST['priemer1'])) {
            if ($_POST['priemer1'] != 0) {
                $sth = $this->db->prepare("UPDATE Students SET priemer1 = :priemer1 WHERE id=:id");
                $priemer1 = $_POST['priemer1'];
                $priemer1_bodka = str_replace(',', '.', $priemer1);
                $sth->bindParam(':priemer1', $priemer1_bodka);
                $sth->bindParam(':id', $id);
                $sth->execute();
                $this->logger->writeToLog('update', 'priemer1', $id, $info, $user, $priemer1_bodka);
            } else {
                $sth = $this->db->prepare("UPDATE Students SET priemer1 = NULL WHERE id=:id");
                $sth->bindParam(':id', $id);
                $sth->execute();
                $this->logger->writeToLog('delete', 'priemer1', $id, $info, $user);
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
                $this->logger->writeToLog('update', 'priemer2', $id, $info, $user, $priemer2_bodka);
            } else {
                $sth = $this->db->prepare("UPDATE Students SET priemer2 = NULL WHERE id=:id");
                $sth->bindParam(':id', $id);
                $sth->execute();
                $this->logger->writeToLog('delete', 'priemer2', $id, $info, $user);
            }
        }
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
