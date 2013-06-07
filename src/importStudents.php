<?php

require 'config.php';
//Priezvisko;Meno;Datum nar.;III. Ročník;IV. Ročník;Forma
//Abx;Marxx;4.10.19xx;1,81;2,15;denná
//Adx;Martxx;2.4.19xx;2,55;1,3;denná
//Adx;Filxx;26.9.19xx;2,18;2,18;denná

$filename = "";

$file = fopen($filename, "r");
if ($file === false) {
    $output->writeln('<error>Failed to open file</error>');
    return;
}

$conn = connect_db();
$line = fgets($file);

try {
    while ($buffer = fgets($file)) {
        $split_line = preg_split("/[;]/", $buffer);

        $priezvisko = trim($split_line[0], "'\"");
        $meno = trim($split_line[1], "'\"");
        $datum = trim($split_line[2], "'\"");
        $priemer1 = trim($split_line[3], "'\"");
        $priemer2 = trim($split_line[4], "'\"");
        $split_line[5] = str_replace("\n", '', $split_line[5]);
        $forma = trim($split_line[5], "'\"");

        $dateParts = preg_split("/[.]+/", $datum);
        $datum = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
        $priemer1 = str_replace(',', '.', $priemer1);
        $priemer2 = str_replace(',', '.', $priemer2);
        
        $fields_values = array(
            'meno' => $meno,
            'priezvisko' => $priezvisko,
            'datum_narodenia' => $datum,
            'priemer1' => $priemer1,
            'priemer2' => $priemer2,
            'forma_studia' => $forma
        );
        $res = $conn->autoExecute('Students', $fields_values, DB_AUTOQUERY_INSERT);
    }
} catch (Exception $e) {
    throw $e;
}


fclose($file);
?>
