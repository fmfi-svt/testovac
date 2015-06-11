<?php
 
require_once __DIR__. '/../config.php';
require_once __DIR__. '/db.php';
//Priezvisko;Meno;Datum nar.;III. RoÄŤnĂ­k;IV. RoÄŤnĂ­k;Forma
//Abx;Marxx;4.10.19xx;1,81;2,15;dennĂˇ
//Adx;Martxx;2.4.19xx;2,55;1,3;dennĂˇ
//Adx;Filxx;26.9.19xx;2,18;2,18;dennĂˇ
$filename = $argv[1];
 
$file = fopen($filename, "r");
if ($file === false) {
    $output->writeln('<error>Failed to open file</error>');
    return;
}
 
$conn = connect_db();
$line = fgets($file);
 
try {
    while ($buffer = fgets($file)) {
        $buffer = trim($buffer);
        $split_line = preg_split("/[;]/", $buffer);
 
        $priezvisko = trim($split_line[0], "'\"");
        $meno = trim($split_line[1], "'\"");
        $datum = trim($split_line[2], "'\"");
        $priemer1 = trim($split_line[3], "'\"");
        $priemer2 = trim($split_line[4], "'\"");
        $forma = trim($split_line[5], "'\"");
 
        if ($datum === '') {
                $datum = '0000-00-00';
        } else {
                $dateParts = preg_split("/[.]+/", $datum);
                $datum = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
        }
        $priemer1 = str_replace(',', '.', $priemer1);
        $priemer2 = str_replace(',', '.', $priemer2);
        if ($priemer1 == 0) {
                $priemer1 = NULL;
        }
        if ($priemer2 == 0) {
                $priemer2 = NULL;        
        }
        $stmt = $conn->prepare("insert into Students (meno,priezvisko,datum_narodenia,priemer1,priemer2,forma_studia) values (:meno,:priezvisko,:datum,:priemer1,:priemer2,:forma)");
        $stmt->bindParam(':meno',$meno);
        $stmt->bindParam(':priezvisko',$priezvisko);
        $stmt->bindParam(':datum',$datum);
        $stmt->bindParam(':priemer1',$priemer1);
        $stmt->bindParam(':priemer2',$priemer2);
        $stmt->bindParam(':forma',$forma);
        executeStmt($stmt);
       
    }
} catch (Exception $e) {
    throw $e;
}
 
fclose($file);
?>
