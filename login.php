<?php
session_start();
include 'users.php';

if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($users[$username] == $password && strlen($username) > 0) {
        $_SESSION['user'] = $_POST["username"];
        $_SESSION['login'] = true;
        header("location:index.php");
    } else {
        echo "Nespravne heslo pre username: $username. ";
        echo "Skuste znova.";
        exit;
    }
}

?>

<html>
    <head>
        <title>Login</title>
    </head>
    <body>
        <div id="containt" align="center">
            <form action="login.php" method="post">
                <div id="header"><h2>C-Server Login</h2></div>
                <table>

                    <tr>
                        <td>Zadaj login:</td>
                        <td> <input type="text" name="username" size="20"></td>
                    </tr>

                    <tr>
                        <td>Zadaj heslo:</td>
                        <td><input type="password" name="password" size="20"></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Log In"></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>