<html>
    <head>
        <title>Login</title>
    </head>
    <body>
        <div id="containt" align="center">
            <form action="index.php" method="post">
                <input type="hidden" name="action" value="login" />
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
