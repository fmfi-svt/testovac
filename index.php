<?php
include 'db.php';

if ($_SESSION['login'] != true) {
   header("location:login.php");
   exit;
}

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
        <!--<script type="text/javascript" src="errorChecking.js"></script>-->   
        <script type="text/javascript" src="jquery.leanModal.min.js"></script>
        <script type="text/javascript">
            $(function() {
                $('a[rel*=leanModal]').leanModal({top: 200, closeButton: ".modal_close"});
            });
        </script>
    </head>
    <body>          
        <div id="header">
            <?php
            if (isset($_SESSION['sprava'])) {
                if (isset($_SESSION['counter'])) {
                    if ($_SESSION['counter'] != 0) {
                        echo $_SESSION['sprava'];
                        $_SESSION['counter'] = $_SESSION['counter'] - 1;
                    } else {
                        echo 'Momentálne sa neodohrali žiadne udalosti.';
                    }
                }
            } else {
                echo 'Momentálne sa neodohrali žiadne udalosti.';
            }
            ?>
            <div id="navigation">

                <a href="priemery.php">Editácia priemerov</a>&nbsp;
                Ste prihlásení ako
                <?php
                echo $_SESSION['user'];
                ?>
                <a href="logout.php">Logout</a>
            </div>
        </div>


        <?php
        //print_r($_POST);
        ?>
        <div id="main">
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
                    <th style="min-width:4em">Čas registrácie</th>
                    <th style="min-width:5em" class="cssright">PID</th>
                </tr>

                <?php
                while ($query1->fetchInto($row)) {
                    $numOfInputs = 0;

                    if ($row['exported'] == 1) {
                        continue;
                    }

                    $id = $row['id'];
                    $meno = $row['meno'];
                    $priezvisko = $row['priezvisko'];
                    $datum = $db->sqlDateToRegular($row['datum_narodenia']);
                    $forma = $row['forma_studia'];
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
                    $pid = $row['pid'];
                    $time = date("d.m.Y H:i:s", strtotime($row['time_of_registration']));
                    if ($row['printed'] == 1) {
                        $printed = 'áno';
                    } else {
                        $printed = 'nie';
                    }
                    $info = $meno . ' ' . $priezvisko;

                    echo "<tr id=\"$id\" class=\"name_listing\">";

                    $id_name = 'id[' . $id . ']';

                    // meno cell
                    echo '<td class="meno">';
                    echo $meno;
                    echo '</td>';

                    // priezvisko cell
                    echo '<td class="priezvisko spec namefilter">';
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

                    // priemer1 cell
                    echo '<td class="priemery">';
                    if ($priemer1 != 0) {
                        echo $priemer1;
                    }
                    echo '</td>';

                    // priemer2 cell
                    echo '<td class="priemery">';
                    if ($priemer2 != 0) {
                        echo $priemer2;
                    }
                    echo '</td>';

                    echo '<td>';
                    echo $printed;
                    echo '</td>';

                    echo '<td>';
                    if ($pid <> 0) {
                        echo $time;
                    }
                    echo '</td>';

                    echo '<td class="pidtd cssright">';
                    if ($pid != 0) {
                        echo $pid;
                    } else {
                        echo 'Neregistrovaný.';
                    }
                    echo '</td>';

                    // modal window pre pridanie PID
                    echo "<div class=\"addPid modal\" id=addPid$id>";
                    echo "<input type=\"hidden\" class=\"idsub\" name=\"$id_name\" value=\"$id\">";
                    echo "<input type=\"hidden\" class=\"infosub\" name=\"info\" value=\"$info\">";
                    echo "<h3>Registrácia uchádzača</h3> Prajete si prideliť zvolenému uchádzačovi zadaný PID? <br/><br/> Meno:  <b>$meno</b><br/>Priezvisko:  <b>$priezvisko</b><br/><br/>";
                    if ($row['printed'] == 1) {
                        echo '<div class="warning"> Pozor, registrujete už vytlačeného študenta!!!</div><br/>';
                    }
                    if ($pid == 0) {
                        echo "PID: <input type=\"text\" id=\"input$id\" size=\"18\" class=\"pidcheck pid pidsub\" name=\"inputText$id\" value=\"\">";
                    } else {
                        echo $pid;
                    }
                    echo '<br/><br/>';
                    echo "<input type=\"button\" class=\"addbtn\" value=\"Áno, prideliť zadaný PID.\">";
                    echo "<input type=\"button\" class=\"closebtn\" value=\"Nie, ponechať bez PID.\">";
                    echo '</div>';

                    // modal window pre vymazanie usera
                    echo "<div class=\"deletePid modal\" id=deletePid$id>";
                    echo "<input type=\"hidden\" class=\"idsub\" name=\"$id_name\" value=\"$id\">";
                    echo "<input type=\"hidden\" class=\"infosub\" name=\"info\" value=\"$info\">";
                    echo "<h1> Zrušenie registrácie </h1> Chcete zrušiť registráciu pre tohto uchádzača?<br/>";
                    echo "<p><b>Meno:</b>  $meno <br/> <b>Priezvisko:</b>  $priezvisko<br/> <b>PID:</b>  $pid</p>";

                    echo "<input type=\"button\" class=\"subdelbtn\" name=\"delete\" value=\"Áno, zruš registráciu.\">";
                    echo "<input type=\"button\" class=\"closebtn\" value=\"Nie, ponechať registráciu.\">";

                    echo '</div>';

                    echo '</tr>';
                    echo '<tr class=hiddentr>';
                    echo '<td>';

                    echo '</td>';
                    echo '<td>';
                    if ($pid <> 0) {
                        echo "<a id=\"go$id\" rel=\"leanModal\" href=\"#deletePid$id\">Zrus registraciu.</a>";
                    } else {
                        echo "<a id=\"go$id\" class=\"addClick\"rel=\"leanModal\" href=\"#addPid$id\">Pridaj PID</a>";
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
                <tr class=hiddentr>
                    <td>
                        <input type="hidden" class="idsub">"
                    </td>
                    <td>
                </tr>
            </table>
        </form>
        </div>
    </body>
</html>
