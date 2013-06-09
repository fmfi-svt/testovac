<?php

session_start();
require_once __DIR__ . '/../src/flash.php';
require_once __DIR__ . '/../src/actions.php';
require_once __DIR__ . '/../src/login.php';
require_once __DIR__ . '/../src/verhoeffChecker.php';

$db = new Repository();

function e($text) {
    return htmlspecialchars($text, 0, 'UTF-8');
}

function renderTemplate($filename, $params = array()) {
    // jednoduche templaty
    foreach ($params as $param => $val) {
        $$param = $val;
    }
    ob_start();
    try {
        include __DIR__ . '/../templates/' . $filename;
        ob_end_flush();
    }
    catch (\Exception $e) {
        ob_end_clean();
        throw $e;
    }
}

function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function param_get($name, $default = null)
{
    if (!isset($_GET[$name])) {
        return $default;
    }
    return $_GET[$name];
}

function param_post($name, $default = null)
{
    if (!isset($_POST[$name])) {
        return $default;
    }
    return $_POST[$name];
}

function redirect($params) {
    $q_params = http_build_query($params);
    if (strlen($q_params) > 0) {
        $q_params = '?' . $q_params;
    }
    header('Location: index.php' . $q_params); // TODO: absolutne URL
}

if (current_user() === null) {
    if (is_post() && param_post('action') === 'login') {
        $username = (string) param_post('username', '');
        $password = (string) param_post('password', '');
        login($username, $password);
        redirect(array('action' => 'zoznam'));
    }
    else if (is_post()) {
        redirect(array('action' => 'login'));
    }
    else {
        renderTemplate('login.php');
    }
    
}
else {
    $templateParams = array('user' => current_user(), 'db' => $db);
    if (is_post()) {
        $action = param_post('action');
        if ($action == 'logout') {
            logout();
            redirect(array('action' => 'login'));
        }
        else if ($action == 'check-pid') {
            $pid = param_post('pidd');
            if ($pid == null) {
                echo 'bad request'; // TODO
            }
            else {
                $vc = new VerhoeffChecker();
                echo $vc->check($pid);
            }
        }
        else if ($action == 'update') {
            $db->updateStudents();
            echo $time = date("d.m.Y H:i:s", time());
            $next = param_post('next');
            if ($next !== null) {
                redirect(array('action' => $next));
            }
        }
    }
    else {
        $action = param_get('action', 'zoznam');
        if ($action == 'logout') {
            logout();
            redirect(array('action' => 'login'));
        }
        else if ($action == 'zoznam') {
            $templateParams['students'] = $db->getAllStudents();
            $templateParams['sprava'] = get_flash_and_clear();
            renderTemplate('zoznam.php', $templateParams);
        }
        else if ($action == 'priemery') {
            $students = $db->getStudentsForAverage();
            
            $before_row = array();
            $after_row = array();

            foreach ($students as $row) {
                $testDateStr = strtotime($row['time_of_registration']);
                $minutesSinceRegistration = ($testDateStr - time()) / 60;

                if ($minutesSinceRegistration > -90) {
                    $before_row[] = $row;
                } else {
                    $after_row[] = $row;
                }
            }
            
            $templateParams['before_row'] = $before_row;
            $templateParams['after_row'] = $after_row;
            $templateParams['sprava'] = get_flash_and_clear();
            renderTemplate('priemery.php', $templateParams);
        }
    }
}