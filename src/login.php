<?php

function login($username, $password)
{
    include __DIR__ . '/../users.php';
    if (empty($users[$username])) {
        return false;
    }
    if (strlen($username) == 0 || strlen($password) == 0) {
        return false;
    }
    if ($users[$username] === $password) {
        $_SESSION['user'] = $_POST["username"];
        $_SESSION['login'] = true;
        return true;
    } else {
        return false;
    }
}

function current_user()
{
    if (empty($_SESSION['user']) || empty($_SESSION['login']) || !$_SESSION['login']) {
        return null;
    }
    return $_SESSION['user'];
}

function logout()
{
    session_start();
    session_unset();
    session_destroy();
}
