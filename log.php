<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="theme.css" /> 
    </head>
    <body>  
        <div class="log">
            <?php
            $data = file_get_contents("logs/db_log.log"); //read the file
            $convert = explode("\n", $data); 

            for ($i = 0; $i < count($convert); $i++) {
                echo $convert[$i]; 
                echo '<br/>';
            }
            ?>
        </div>
    </body>
</html>