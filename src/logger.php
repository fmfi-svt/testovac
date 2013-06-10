<?php

require_once(__DIR__ . '/db.php');

class Logger {

    private $dbh;
    private $log_location;

    function __construct($db_handle) {
        $this->dbh = $db_handle;
        $this->log_location = __DIR__ . '/../logs/log.log';
    }

    function writeToLog($action, $what, $id, $name, $who, $value = '_no_value_') {
        if ($action == 'update') {
            $msg = 'UPDATE ' . $what . ' for ID/name: ' . $id . '/' . $name . ' | new value: ' . $value .
                    ' | by user: ' . $who . ' | at ' . date("d.m.Y H:i:s", time());
        } else if ($action == 'delete') {
            $msg = 'DELETE ' . $what . ' for ID/name/PID: ' . $id . '/' . $name . '/' . $value .
                    ' | by user: ' . $who . ' | at ' . date("d.m.Y H:i:s", time());
        } else if ($action == 'print') {
            $msg = 'PRINT ' . $what . ' for these PIDs: ' . $value .
                    ' | by user: ' . $who . ' | at ' . date("d.m.Y H:i:s", time());
        } else if ($action == 'export') {
            $msg = 'EXPORT ' . $what . ' | by user: ' . $who . ' | at ' . date("d.m.Y H:i:s", time());
        }

        $stmt = $this->dbh->prepare("INSERT INTO Log (action, changed_item, student_id, student_name, new_value, user)
                values (:action,:changed_item, :student_id, :student_name, :new_value, :user)");
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':changed_item', $what);
        $stmt->bindParam(':student_id', $id);
        $stmt->bindParam(':student_name', $name);
        $stmt->bindParam(':new_value', $value);
        $stmt->bindParam(':user', $who);
        executeStmt($stmt);

        $msg .= " \n";
        $handle = fopen($this->log_location, 'a');
        if ($handle === false) {
            echo "Neuspesne otvorenie suboru: " . $log_location . "\n";
            return;
        }
        fputs($handle, $msg);
        fclose($handle);
    }

}

?>
