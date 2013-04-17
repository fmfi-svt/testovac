<?php
include 'db.php';

if (isset($_POST['id'])) {
    header("location: index.php");
}
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
                $('a[rel*=leanModal]').leanModal({ top : 200, closeButton: ".modal_close" });		
            });
        </script>
    </head>
    <body>        
        <div id="header">

            <div id="navigation">

                <a href="index.php">Pridavanie priemerov</a>&nbsp;
                Ste prihlásení ako 
                <?php
                echo $_SERVER['REMOTE_USER'];
                ?>
            </div>
        </div>

        <?php
        if (isset($_SESSION['sprava'])) {
            if (isset($_SESSION['counter'])) {
                if ($_SESSION['counter'] != 0) {
                    echo $_SESSION['sprava'];
                    $_SESSION['counter'] = $_SESSION['counter'] - 1;
                } else {
                    echo 'Momentálne sa neodohrali žiadne udalosti zasluhujúce upozornenie.';
                }
            }
        } else {
            echo 'Momentálne sa neodohrali žiadne udalosti zasluhujúce upozornenie.';
        }
        ?>
    </div>
    <?php
    //print_r($_POST);
    ?>
    <form id="form" name="studentform" method="post">
        <table id="mytable" cellspacing="0">
            <tr>
                <th style="min-width:12em" class="cssleft">Meno</th>
                <th style="min-width:18em">Priezvisko</th>
                <th style="min-width:8em">Dátum nar.</th>
                <th style="min-width:6em">Forma</th>
                <th style="min-width:6em" class="cssright"></th>
            </tr>

            <?php
            while ($query->fetchInto($row)) {
                $numOfInputs = 0;

                if ($row['exported'] == 1) {
                    continue;
                }

                $id = $row['id'];
                $meno = $row['meno'];
                $priezvisko = $row['priezvisko'];
                $datum = $db->sqlDateToRegular($row['datum_narodenia']);
                $forma = $row['forma_studia'];

                $pid = $row['pid'];
                $info = $meno . ' ' . $priezvisko;

                echo "<tr class=\"name_listing\">";

                $id_name = 'id[' . $id . ']';

                // meno cell
                echo '<td>';
                echo $meno;
                echo '</td>';

                // priezvisko cell
                echo '<td class="spec namefilter">';
                echo "<b>$priezvisko</b>";
                echo '</td>';

                // datum cell
                echo '<td>';
                echo $datum;
                echo '</td>';

                // forma cell
                echo '<td>';
                echo $forma;
                echo '</td>';

                echo '<td>';
                if ($pid<>0) {
                    echo 'Registrovaný.';
                } else {
                    echo "<a id=\"go\" rel=\"leanModal\" name=\"test.$id\" href=\"#test$id\">Pridaj PID</a>";
                }
                echo '</td>';

                echo "<div class=\"test\" id=test$id>";
                echo "<input type=\"hidden\" class=\"idsub\" name=\"$id_name\" value=\"$id\">";
                echo "<input type=\"hidden\" class=\"infosub\" name=\"info\" value=\"$info\">";
                echo "Meno:  $meno";
                echo '<br/>';
                echo "Priezvisko:  $priezvisko";
                echo '<br/>';
                if ($pid == 0) {
                    $pid_name = 'pid[' . $id . ']';
                    echo "<input type=\"text\" class=\"pidcheck pid pidsub\" name=\"$pid_name\" value=\"\">";
                    $numOfInputs++;
                } else {
                    echo $pid;
                }
                $submit_name = 'sub[' . $id . ']';
                if ($numOfInputs > 0) {
                    echo "<input type=\"button\" class=\"subbtn\" name=\"$submit_name\" value=\"Uložiť\">";
                    echo "<input type=\"hidden\" class=\"sub\" name=\"$submit_name\" value=\"\">";
                }
                echo '</div>';

                echo '</tr>';
            }
            ?>
        </table>
    </form>
</body>
</html>
