<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="./css/theme.css" />

        <script src="./js/jquery.min.js"></script>
        <script type="text/javascript" src="./js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="./js/script.js"></script>
        <script type="text/javascript" src="./js/errorChecking.js"></script>   
        <script type="text/javascript" src="./js/jquery.leanModal.min.js"></script>
        <script type="text/javascript">
            $(function() {
                $('a[rel*=leanModal]').leanModal({top: 200, closeButton: ".modal_close"});
            });
        </script>
    </head>
    <body>        
        <div id="header">

            <div id="navigation">
                <a href="index.php">Prideľovanie PID (registrácia)</a>&nbsp;
                Ste prihlásení ako <?php echo e($user) ?>
                <a href="index.php?action=logout">Logout</a>
            </div>
        </div>
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
                    <th style="min-width:5em">Exportovaný</th>
                    <th style="min-width:4em">Čas registrácie</th>
                    <th style="min-width:5em" class="cssright">PID</th>
                </tr>

                <tr>
                    <td colspan="10"><b>Uchádzači zaregistrovaní za posledných 90 minút</b></td>
                </tr>


                <?php

                function oneRow($row, $db) {

                    $time = date("d.m.Y H:i:s", strtotime($row['time_of_registration']));

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
                    echo "<input type=\"hidden\" class=\"idsub\" name=\"".e($id_name)."\" value=\"".e($id)."\">";
                    echo "<input type=\"hidden\" class=\"infosub\" name=\"info\" value=\"".e($info)."\">";
                    // meno cell
                    echo '<td>';
                    echo e($meno);
                    echo '</td>';

                    // priezvisko cell
                    echo '<td class="spec namefilter">';
                    echo e($priezvisko);
                    echo '</td>';

                    // datum cell
                    echo '<td>';
                    echo e($datum);
                    echo '</td>';

                    // forma cell
                    echo '<td>';
                    echo e($forma);
                    echo '</td>';

                    // priemer1 cell
                    echo '<td class="priemery priemer1td">';
                    $priemer1_name = 'priemer1[' . $id . ']';
                    if ($priemer1 == 0) {
                        $priemer1_value = '';
                    } else {
                        $priemer1_value = (string) $priemer1;
                    }
                    echo "<input type=\"text\" class=\"priemer1check priemerinput\" size=\"3\" name=\"".e($priemer1_name)."\" value=\"".e($priemer1_value)."\">";
                    echo '</td>';

                    // priemer2 cell
                    echo '<td class="priemery priemer2td">';
                    $priemer2_name = 'priemer2[' . $id . ']';
                    if ($priemer2 == 0) {
                        $priemer2_value = '';
                    } else {
                        $priemer2_value = (string) $priemer1;
                    }
                    echo "<input type=\"text\" class=\"priemer2check priemerinput\" size=\"3\" name=\"".e($priemer2_name)."\" value=\"".e($priemer2)."\">";
                    echo '</td>';

                    echo '<td>';
                    echo e($printed);
                    echo '</td>';

                    echo '<td>';
                    echo e($exported);
                    echo '</td>';

                    echo '<td>';
                    if ($pid <> 0) {
                        echo e($time);
                    }
                    echo '</td>';

                    // pid cell
                    echo '<td class="pidtd cssright">';
                    if ($pid != 0) {
                        echo e($pid);
                    } else {
                        echo 'Neregistrovaný.';
                    }
                    echo '</td>';
                    echo '</tr>';
                }

                foreach ($before_row as $row) {
                    oneRow($row, $db);
                }
                ?>

                <tr>
                    <td colspan="10"><b>Uchádzači zaregistrovaní skôr</b></td>
                </tr>

                <?php
                foreach ($after_row as $row) {
                    oneRow($row, $db);
                }
                ?>
            </table>
        </form>
    </div>
    </body>
</html>
