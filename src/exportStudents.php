#!/usr/bin/php

<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/db.php';

$db = connect_db();
$logger = new Logger($db);
$fileloc = __DIR__ . "/../output/cbody.php";

echo "Command exportStudents currently in progress...\n\n";

$handle = fopen($fileloc, 'w');
if ($handle === false) {
    echo "Neuspesne otvorenie suboru: ". $fileloc . "\n";
    return;
} else {
    echo "Uspesne vytvoreny subor: ". $fileloc . "\n";;
}

$students = $db->query('SELECT * from Students WHERE printed = 1 ORDER BY pid');
executeSQL($db, 'UPDATE Students SET exported = 1 WHERE exported = 0 AND printed = 1');

$logger->writeToLog('export', 'student averages', null, null, 'administrator');

$msg = '<?php ' . "\n";
$msg = $msg . '$bodyC = array(' . " \n";
$counter = 0;
$studentsWithoutPid = 0;
try {
    foreach ($students as $row) {
        $counter++;
        $priemer1 = $row['priemer1'];
        $priemer2 = $row['priemer2'];
        $forma = $row['forma_studia'];
        if ($forma == 'denná') {
            $forma = 'denna';
        } else {
            $forma = 'externa';
        }
        $pid = $row['pid'];
        if ($pid == null) { 
            $studentsWithoutPid++;
            continue;
        }
        $body1 = $priemer1 * 100;
        $body2 = $priemer2 * 100;
        if ($body1 != 0) {
            if ($body1 <= 300) {
                $body1 = 49 - floor(($body1 - 101) / 5);
            } else {
                $body1 = 9 - floor(($body1 - 301) / 10);
            }
        } else {
            echo "Student s pid $pid nema zadany priemer1 !!! \n";
        }
        if ($body2 != 0) {
            if ($body2 <= 300) {
                $body2 = 49 - floor(($body2 - 101) / 5);
            } else {
                $body2 = 9 - floor(($body2 - 301) / 10);
            }
        } else {
            echo "Student s pid $pid nema zadany priemer2 !!! \n";
        }

        $body = $body1 + $body2;
        $msg = $msg . "\t" . '\'' . $pid . '\'' . " => array( \n";
        $msg = $msg . "\t \t" . '\'body\' => ' . $body . ',' . "\n";
        $msg = $msg . "\t \t" . '\'forma\' => ' . '\'' . $forma . '\'' . "\n";
        $msg = $msg . "\t" . '),' . "\n";
    }
} catch (Exception $e) {
    throw $e;
}
$msg = $msg . ');' . "\n";
$msg = $msg . '?>' . "\n";
//print_r($msg);
echo "POZOR! V databaze sa nachadza $studentsWithoutPid vytlacenych studentov so zrusenou registraciou! \n";
echo "\nUspesne vyexportovane body za priemery pre $counter studentov. \n";

fputs($handle, $msg);
fclose($handle);
?>
