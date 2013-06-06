#!/usr/bin/php

<?php
require_once __DIR__ . '/../config.php';

$db = connect_db();

$students = $db->query('SELECT * from Students WHERE printed = 1 ORDER BY pid');
$db->exec('UPDATE Students SET exported = 1 WHERE exported = 0 AND printed = 1');
$logmsg = 'EXPORT; ';

$logmsg = $logmsg . " edit_by:admin , time:" . date('G-i-s+j/m/y');

// $this->writeToLog($logmsg);

$fileloc = __DIR__ . "/../output/cbody.php";
$handle = fopen($fileloc, 'w');
$msg = '<?php ' . "\n";
$msg = $msg . '$bodyC = array(' . " \n";

try {
    foreach ($students as $row) {
        $priemer1 = $row['priemer1'];
        $priemer2 = $row['priemer2'];
        $forma = $row['forma_studia'];
        if ($forma == 'denná') {
            $forma = 'denna';
        } else {
            $forma = 'externa';
        }
        $pid = $row['pid'];
        $body1 = $priemer1 * 100;
        $body2 = $priemer2 * 100;
        if ($body1 != 0) {
            if ($body1 <= 300) {
                $body1 = 49 - floor(($body1 - 101) / 5);
            } else {
                $body1 = 9 - floor(($body1 - 301) / 10);
            }
        } else {
            print_r("\nStudent s pid $pid nema zadany priemer1 !!! \n");
        }
        if ($body2 != 0) {
            if ($body2 <= 300) {
                $body2 = 49 - floor(($body2 - 101) / 5);
            } else {
                $body2 = 9 - floor(($body2 - 301) / 10);
            }
        } else {
            print_r("\nStudent s pid $pid nema zadany priemer2 !!! \n");
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
print_r($msg);
fputs($handle, $msg);
fclose($handle);
?>
