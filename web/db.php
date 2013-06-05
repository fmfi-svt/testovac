<?php

session_start();
require_once __DIR__ . '/../src/actions.php';

$db = new Repository();

// checkni, ci sa nieco nepridalo do POST-u
if (isset($_POST['id'])) {
    $db->updateStudents();
}

