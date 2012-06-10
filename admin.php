<?php
include 'db.php';
if (isset($_POST['id'])) {
    header("location: admin.php");
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
        <script type="text/javascript" src="admin.js"></script>
        <script type="text/javascript" src="errorChecking.js"></script>      
        <script type="text/javascript" src="diacritics.js"></script>  
    </head>
    <body>
        <div id="header">
            <div id="student-filter-wrapper">
                Filtrovať študentov: <input type="text" id="student-filter"/> 
                <input type="button" id="reset-button" value="Reset"/> 
            </div>

            <div class="filterbar">
                <input id="r1" type="radio" class="filterforext" name="filterchoice" value="externi" />
                <label for="r1">Externí </label>&nbsp;
                <input id="r2" type="radio" class="filterforden" name="filterchoice" value="denni" />
                <label for="r2">Denní </label>&nbsp;
                <input id="r3" type="radio" class="filterforall" name="filterchoice" value="vsetci" />
                <label for="r3">Všetci </label>&nbsp;
            </div>
            <div id="navigation">
                <a href="log.php">Pozriet log</a>&nbsp;
                <a href="index.php">Normálny mód</a>&nbsp;
                Ste prihlásení ako 
                <?php
                echo $_SERVER['REMOTE_USER'];
                ?>
            </div>
        </div>
<!--        <input type="button" id="add-student-btn" name="" value="Pridaj studenta" />
        <div id="add-student" class="hide">
            <form id="add-form" name="addstudentform" method="post">
                Meno: <input type="text" class="add-name" name="add-name" value="" /> <br />
                Priezvisko: <input type="text" class="add-surname" name="add-surname" value="" /> <br />
                Datum narodenia: <input type="text" class="add-date" name="add-date" value="" /> <br />
                Priemer 1: <input type="text" class="add-priemer1" name="add-priemer1" value="" /> <br />
                Priemer 2: <input type="text" class="add-priemer2" name="add-priemer2" value="" /> <br />  
                Forma studia: <input type="text" class="add-forma" name="add-forma" value="" /> <br />  
                <input type="submit" class="sub-add" name="sub-add" value="save" /> <br />  
            </form>
        </div>-->

        <div id="statusbar">
            Status bar:&nbsp;
            <?php
            // nefunguje
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
        <form id="form" name="studentform" method="post">
            <table id="mytable" cellspacing="0">
                <tr>
                    <th style="min-width:3em" class="cssleft">ID</th>
                    <th style="min-width:12em">Meno</th>
                    <th style="min-width:18em">Priezvisko</th>
                    <th style="min-width:6em">Dátum nar.</th>
                    <th style="min-width:6em">Forma</th>
                    <th style="min-width:5em">III. roč.</th>
                    <th style="min-width:5em">IV. roč.</th>
                    <th style="min-width:14em">PID</th>
                    <th style="min-width:6em">Vytlačený</th>
                    <th style="min-width:6em">Exportovaný</th>
                    <th style="min-width:6em" class="cssright"></th>
                </tr>

                <?php
                while ($query->fetchInto($row)) {

                    $id = $row['id'];
                    $meno = $row['meno'];
                    $priezvisko = $row['priezvisko'];
                    $datum = $db->sqlDateToRegular($row['datum_narodenia']);
                    $priemer1 = $row['priemer1'];
                if ($priemer1 != 0) {
   		    if (preg_match("/^\d$/", $priemer1) == 1) {
                        $priemer1 = $priemer1 . '.00';
                    } else if (preg_match("/^\d[.]\d$/", $priemer1) == 1) {
                        $priemer1 = $priemer1 . '0';
                    }
		} else {
			$priemer1 = '';
		}
                    $priemer2 = $row['priemer2'];
		if ($priemer2 != 0) {
                    if (preg_match("/^\d$/", $priemer2) == 1) {
                        $priemer2 = $priemer2 . '.00';
                    } else if (preg_match("/^\d[.]\d$/", $priemer2) == 1) {
                        $priemer2 = $priemer2 . '0';
                    }
		} else {
			$priemer2 = '';
		}
                    $forma = $row['forma_studia'];
                    if ($forma == 'externá') {
                        $formaclass = 'ext';
                    } else {
                        $formaclass = 'int';
                    }
                    $pid = $row['pid'];
                    $printed = $row['printed'];
                    if ($printed == 0) {
                        $printed = 'nie';
                    } else {
                        $printed = 'áno';
                    }
                    $exported = $row['exported'];
                    if ($exported == 0) {
                        $exported = 'nie';
                    } else {
                        $exported = 'áno';
                    }

                    // defaultne zobrazovanie, ziadne inputy
                    echo "<tr class=\"name_listing unlockable $formaclass\">";

                    // id cell
                    echo '<td>';
                    $id_name = 'id[' . $id . ']';
                    echo $id;
                    echo "<input type=\"hidden\" class=\"idsub\" name=\"$id_name\" value=\"$id\">";
                    echo '</td>';

                    // meno cell
                    echo '<td>';
                    echo $meno;
                    echo '</td>';

                    // priezvisko cell
                    echo '<td class="spec">';
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
                    echo '<td class="priemery">';
                    if ($priemer1 != '') { echo $priemer1; }
                    echo '</td>';

                    // priemer2 cell
                    echo '<td class="priemery">';
                    if ($priemer2 != '') { echo $priemer2; }
                    echo '</td>';

                    // pid cell
                    echo '<td class="pidtd">';
                    echo $pid;
                    echo '</td>';

                    // printed cell
                    echo '<td>';
                    echo $printed;
                    echo '</td>';

                    // pid cell
                    echo '<td>';
                    echo $exported;
                    echo '</td>';

                    //  unlock cell
                    echo '<td class="cssunlock">';
                    $unlock_name = 'unlock[' . $id . ']';
                    echo "<input type=\"button\" class=\"unlock_btn\" name=\"$unlock_name\" value=\"Unlock\">";
                    echo '</td>';

                    echo '</tr>';


                    ///////// INPUTY
                    echo '<tr class="hide input_row">';

                    $info = $meno . ' ' . $priezvisko;
                    echo "<input type=\"hidden\" class=\"infosub\" name=\"info\" value=\"$info\">";
                    echo "<input type=\"hidden\" class=\"infoprinted\" name=\"infoprinted\" value=\"$printed\">";
                    echo "<input type=\"hidden\" class=\"infoexported\" name=\"infoexported\" value=\"$exported\">";

                    // id cell
                    echo '<td>';
                    echo $id;
                    $id_name = 'id[' . $id . ']';
                    echo "<input type=\"hidden\" name=\"$id_name\" class=\"idsub\" value=\"$id\">";
                    echo '</td>';

                    // meno cell
                    echo '<td>';
                    $meno_name = 'meno[' . $id . ']';
                    echo "<input type=\"text\" size=\"12\" class=\"menosub menocheck\" name=\"$meno_name\" value=\"$meno\">";
                    echo '</td>';

                    // priezvisko cell
                    echo '<td class="spec">';
                    $priezvisko_name = 'priezvisko[' . $id . ']';
                    echo "<input type=\"text\" size=\"16\" class=\"priezviskosub priezviskocheck\" name=\"$priezvisko_name\" value=\"$priezvisko\">";
                    echo '</td>';

                    // datum cell
                    echo '<td>';
                    $datum_narodenia_name = 'datum_narodenia[' . $id . ']';
                    echo "<input type=\"text\" class=\"datecheck datumsub\" size=\"10\" name=\"$datum_narodenia_name\" value=\"$datum\">";
                    echo '</td>';

                    // forma cell
                    echo '<td>';
                    $forma_studia_name = 'forma_studia[' . $id . ']';
                if ($row['duplicate'] == null) {
		    echo "<select name=\"$forma_studia_name\" class=\"formasub\">";
                    if ($forma == 'denná') {
                        echo "<option value=\"denná\" selected>denná</option>
                        <option value=\"externá\">externá</option>";
                    } else {
                        echo "<option value=\"denná\">denná</option>
                        <option value=\"externá\" selected>externá</option>";
                    }
		} else {
			echo $forma;
		}
                    echo '</select>';
                    echo '</td>';

                    // priemer1 cell
                    echo '<td>';
                    $priemer1_name = 'priemer1[' . $id . ']';
                    echo "<input type=\"text\" class=\"priemer1check priemer1sub\" size=\"4\" name=\"$priemer1_name\" value=\"$priemer1\">";
                    echo '</td>';

                    // priemer2 cell
                    echo '<td>';
                    $priemer2_name = 'priemer2[' . $id . ']';
                    echo "<input type=\"text\" class=\"priemer2check priemer2sub\" size=\"4\" name=\"$priemer2_name\" value=\"$priemer2\">";
                    echo '</td>';

                    // pid cell
                    echo '<td>';
                    $pid_name = 'pid[' . $id . ']';
                    echo "<input type=\"text\" size=\"19\" name=\"$pid_name\" class=\"pidcheck pidsub\" value=\"$pid\">";
                    echo '</td>';

                    // printed cell
                    echo '<td>';
                    echo $printed;
                    echo '</td>';

                    // exported cell
                    echo '<td>';
                    echo $exported;
                    echo '</td>';

                    echo '<td class="cssunlock">';
                    $submit_name = 'sub[' . $id . ']';
                    echo "<input type=\"button\" class=\"subbtn\" name=\"$submit_name\" value=\"Ulozit\">";
                    echo "<input type=\"hidden\" class=\"sub\" name=\"$submit_name\" value=\"\">";
                    echo '</td>';

                    echo '</tr>';
                }
                ?>
            </table>
        </form>
    </body>
</html>
