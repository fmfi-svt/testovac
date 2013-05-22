<?php

session_start();
require_once 'actions.php';

$db = new Repository();

// checkni, ci sa nieco nepridalo do POST-u
if (isset($_POST['id'])) {
    $db->updateStudents();
}

$query1 = $db->getAllStudents();
$query2 = $db->getStudentsForAverage();
