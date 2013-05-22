<?php
include 'db.php';

//if (isset($_POST['id'])) {
//    header("location: priemery.php");
//}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="theme.css" />

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
        <script type="text/javascript" src="script.js"></script>
        <script type="text/javascript" src="errorChecking.js"></script>   
        <script type="text/javascript" src="jquery.leanModal.min.js"></script>
        <script type="text/javascript">
            $(function() {
                $('a[rel*=leanModal]').leanModal({top: 200, closeButton: ".modal_close"});
            });
        </script>
    </head>
    <body>        
        <div id="header">

            <div id="navigation">
                <a href="index.php">Pridávanie PID / Registrácia</a>&nbsp;
                Ste prihlásení ako 
                <?php
                echo $_SERVER['REMOTE_USER'];
                ?>
            </div>
        </div>

        <form id="form" name="studentform" method="post">
            <table id="mytable" cellspacing="0">
                <tr>
                    <th style="min-width:12em" class="cssleft">Meno</th>
                    <th style="min-width:18em">Priezvisko</th>
                    <th style="min-width:8em">Dátum nar.</th>
                    <th style="min-width:6em">Forma</th>
                    <th style="min-width:5em">III. roč.</th>
                    <th style="min-width:5em">IV. roč.</th>
                    <th style="min-width:5em">Vytlačený</th>
                    <th style="min-width:5em">Exportovaný</th>
                    <th style="min-width:4em">Čas registrácie</th>
                    <th style="min-width:5em" class="cssright">PID</th>
                </tr>

                <?php
                while ($query2->fetchInto($row)) {
                    $numOfInputs = 0;


                    $id = $row['id'];
                    $meno = $row['meno'];
                    $priezvisko = $row['priezvisko'];
                    $datum = $db->sqlDateToRegular($row['datum_narodenia']);
                    $priemer1 = $row['priemer1'];
                    if (preg_match("/^\d$/", $priemer1) == 1) {
                        $priemer1 = $priemer1 . '.00';
                    } else if (preg_match("/^\d[.]\d$/", $priemer1) == 1) {
                        $priemer1 = $priemer1 . '0';
                    }
                    $priemer2 = $row['priemer2'];
                    if (preg_match("/^\d$/", $priemer2) == 1) {
                        $priemer2 = $priemer2 . '.00';
                    } else if (preg_match("/^\d[.]\d$/", $priemer2) == 1) {
                        $priemer2 = $priemer2 . '0';
                    }
                    $forma = $row['forma_studia'];

                    $time = $row['time_of_registration'];
                    $pid = $row['pid'];
                    $info = $meno . ' ' . $priezvisko;

                    if ($row['printed'] == 1) {
                        $printed = 'ano';
                    } else {
                        $printed = 'nie';
                    }
                    if ($row['exported'] == 1) {
                        $exported = 'ano';
                    } else {
                        $exported = 'nie';
                    }
                    echo "<tr id=\"$id\" class=\"name_listing\">";

                    $id_name = 'id[' . $id . ']';
                    echo "<input type=\"hidden\" class=\"idsub\" name=\"$id_name\" value=\"$id\">";
                    echo "<input type=\"hidden\" class=\"infosub\" name=\"info\" value=\"$info\">";
                    // meno cell
                    echo '<td>';
                    echo $meno;
                    echo '</td>';

                    // priezvisko cell
                    echo '<td class="spec namefilter">';
                    echo $priezvisko;
                    echo '</td>';

                    // datum cell
                    echo '<td>';
                    echo $datum;
                    echo '</td>';

                    // forma cell
                    echo '<td>';
                    echo $forma;
                    echo '</td>';

                    // priemer1 cell
                    echo '<td class="priemery priemer1td">';
                    if ($priemer1 == 0) {
                        $priemer1_name = 'priemer1[' . $id . ']';
                        echo "<input type=\"text\" class=\"priemer1check priemerinput \" size=\"3\" name=\"$priemer1_name\" value=\"\">";
                        $numOfInputs++;
                    } else {
                        echo "<input type=\"text\" class=\"priemer1check priemerinput\" size=\"3\" name=\"$priemer1_name\" value=\"$priemer1\">";
                    }
                    echo '</td>';

                    // priemer2 cell
                    echo '<td class="priemery priemer2td">';
                    if ($priemer2 == 0) {
                        $priemer2_name = 'priemer2[' . $id . ']';
                        echo "<input type=\"text\" class=\"priemer2check priemerinput\" size=\"3\" name=\"$priemer2_name\" value=\"\">";
                        $numOfInputs++;
                    } else {
                        echo "<input type=\"text\" class=\"priemer2check priemerinput\" size=\"3\" name=\"$priemer2_name\" value=\"$priemer2\">";
                    }
                    echo '</td>';

                    echo '<td>';
                    echo $printed;
                    echo '</td>';

                    echo '<td>';
                    echo $exported;
                    echo '</td>';

                    echo '<td>';
                    if ($pid <> 0) {
                        echo $time;
                    }
                    echo '</td>';

                    // pid cell
                    echo '<td class="pidtd cssright">';
                    if ($pid != 0) {
                        echo $pid;
                    } else {
                        echo 'Neregistrovaný.';
                    }
                    echo '</td>';

                    echo '</tr>';
                }
                ?>
            </table>
        </form>
    </body>
</html>
