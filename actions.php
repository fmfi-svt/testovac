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
        $this->db->query('SET NAMES UTF8;');
        $query = & $this->db->query('SELECT * from Students WHERE printed is null AND pid is not null ORDER BY pid');
        $retquery = & $this->db->query('SELECT * from Students WHERE printed is null AND pid is not null ORDER BY pid');
        $printquery = $this->db->query('UPDATE Students SET printed = 1 WHERE printed is null AND pid is not null');
        // Always check that result is not an error
        if (PEAR::isError($query)) {
            die($query->getMessage());
        }
        
	$logmsg = 'PRINT; ';
	while ($query->fetchInto($row)) {
		$pid = $row['pid'];
		$logmsg = $logmsg . $pid . ' ';
	}
        
        $logmsg = $logmsg . " , edit_by:admin , time:" . date('G-i-s+j/m/y') ;
      	
        $this->writeToLog($logmsg);
	return $retquery;
    }
   

    function exportStudents() {
        $this->db->query('SET NAMES UTF8;');
        $query = & $this->db->query('SELECT * from Students WHERE printed is not null ORDER BY pid');
        $exportquery = $this->db->query('UPDATE Students SET exported = 1 WHERE exported is null AND printed is not null');
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
        if (isset($_POST['meno'])) {
            $sth = $this->db->prepare("UPDATE Students SET meno = (?) WHERE id=" . $id);
            $data = $_POST['meno'];
            $this->db->execute($sth, $data);
            $logMessage = $logMessage . " , meno:" . $data;
        }
        if (isset($_POST['priezvisko'])) {
            $sth = $this->db->prepare("UPDATE Students SET priezvisko = (?) WHERE id=" . $id);
            $data = $_POST['priezvisko'];
            $this->db->execute($sth, $data);
            $logMessage = $logMessage . " , priezvisko:" . $data;
        }
        if (isset($_POST['forma'])) {
            $sth = $this->db->prepare("UPDATE Students SET forma_studia = (?) WHERE id=" . $id);
            $data = $_POST['forma'];
            $this->db->execute($sth, $data);
            $logMessage = $logMessage . " , forma:" . $data;
        }
        if (isset($_POST['datum'])) {
            $sth = $this->db->prepare("UPDATE Students SET datum_narodenia = (?) WHERE id=" . $id);
            $data = $_POST['datum'];
            $this->db->execute($sth, $this->regularDateToSql($data));
            $logMessage = $logMessage . " , datum:" . $data;
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

    function addNewStudent() {
        if (isset($_POST['add-name']) && isset($_POST['add-surname']) && isset($_POST['add-date'])
                && isset($_POST['sub-add'])) {
            $fields_values = array(
                'meno' => $_POST['add-name'],
                'priezvisko' => $_POST['add-surname'],
                'datum_narodenia' => $_POST['add-date'],
                'priemer1' => $_POST['add-priemer1'],
                'priemer2' => $_POST['add-priemer2'],
                'forma_studia' => $_POST['add-forma']
            );
            $res = $this->db->autoExecute('Students', $fields_values, DB_AUTOQUERY_INSERT);

            if (PEAR::isError($res)) {
                die($res->getMessage());
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