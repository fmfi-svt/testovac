<?php

function login($username, $password)
{
    include __DIR__ . '/users.php';
    if ($users[$username] == $password && strlen($username) > 0) {
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