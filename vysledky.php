<?php
$page = 'vysledky';
require 'include/header.php';

function pid2anchor($pid) {
    return 'pid'.$pid;
}

?>

<h1>Výsledky</h1>
<p>
    Priebežné a finálne poradie uchádzačov bude zverejnené na tejto stránke podľa vnútorného predpisu Univerzity Komenského č. 6/2011.
</p>

<p>
Výsledky na tejto stránke sú len informatívne, rozhodujúce
je rozhodnutie dekana o výsledku prijímacieho konania, ktoré bude uchádzačovi
doručené do vlastných rúk.
</p>

<?php
    @include_once __DIR__."/body/cbody.php";
    @include_once __DIR__."/body/hbody.php";

    if (isset($bodyC) && isset($bodyH)) {

	$vybranyKod = empty($_GET['pid'])?null:$_GET['pid'];
	$vybranaForma = 'denna';
        if ($vybranyKod !== null) {
            $vybranyKod = preg_replace('@[^0-9]@', '', $vybranyKod);
            $vybranyKod = preg_replace('@([0-9]{4})([0-9]{4})([0-9]{4})([0-9]{4})@', '\1-\2-\3-\4', $vybranyKod);
        }
	
	$body = array('denna' => array(), 'externa' => array());
	foreach ($bodyC as $kod => $info) {
		if ($kod == $vybranyKod) $vybranaForma = $info['forma'];
		if (!in_array($info['forma'], array_keys($body))) throw new Exception('Neplatná forma štúdia vo výsledkoch: '.$info['forma']);
		$body[$info['forma']][$kod] = $info['body'];
	}
	
	foreach ($bodyH as $kod => $zlomok) {
		if (isset($body['denna'][$kod])) $body['denna'][$kod] = round($body['denna'][$kod]+($zlomok[0]/$zlomok[1]),3);
		else if (isset($body['externa'][$kod])) $body['externa'][$kod] = round($body['externa'][$kod]+($zlomok[0]/$zlomok[1]),3);
		else throw new Exception('Nenašli sa body z vysvedčenia pre jeden z kódov.');
	}

	arsort($body['denna']);
	arsort($body['externa']);
?>
<script>
	function show(id) {
		document.getElementById(id).style.display='block';
	}
	function hide(id) {
		document.getElementById(id).style.display='none';
	}
	function scrollTo(id, offset) {
		offset = offset | 0;
		var el = document.getElementById(id);
		if (!el) return;
		var curtop = 0;
		if (el.offsetParent) {
			do {
				curtop += el.offsetTop;
			} while (el = el.offsetParent);	
		}
		window.scroll(0, curtop - offset);
	}
	window.onload = function() {
		scrollTo("<?php echo pid2anchor($vybranyKod); ?>", 50);
	}
</script>
<hr/>
<p>
Zvýrazni výsledok podľa kódu:
<form action="" method="GET">
<input id="pid" name="pid" value="<?php echo htmlspecialchars($vybranyKod, ENT_QUOTES, 'UTF-8');?>"/>
<button type="submit">Vyhľadaj</button>
</form>
<?php
if ($vybranyKod!==null && empty($body[$vybranaForma][$vybranyKod])) {
	echo '<br/><span class="warning">Zadaný kód sa vo výsledkovej listine nenašiel.</span>';
}
?>
</p>
<hr/>
<h2>Priebežné výsledky prijímacích pohovorov, 11.6.2013</h2>
<div id="denna-forma"<?php if ($vybranaForma != 'denna') echo ' style="display:none;"'?>>
<p><a href="javascript:hide('denna-forma');show('externa-forma');void(0);">zobraz výsledky pre externú formu</a></p>
<h2>Denná forma štúdia</h2>
<table>
<tr><td>poradie</td><td>pid</td><td>body</td></tr>
<?php
function vypisTabulku($body, $forma, $vybranaForma, $vybranyKod) {
	$poradie = 0;
	$vypisporadie = 0;
	$posledne = -1;
	foreach ($body[$forma] as $pid => $skore) {
		$poradie++;
		if ($skore != $posledne) $vypisporadie = $poradie; 
		$posledne = $skore;
		if ($vybranaForma == $forma && $vybranyKod == $pid) {
			echo '<tr class="selected-score">';
		}
		else echo "<tr>";
		$escapedPid = htmlspecialchars($pid, 0, 'UTF-8');
		printf("<td id=\"%s\" align=right>%d.</td><td>%s</td><td>%.03f</td></tr>\n",
			pid2anchor($pid), $vypisporadie, $escapedPid, $skore);
	}
}

vypisTabulku($body, 'denna', $vybranaForma, $vybranyKod);
?>
</table>
</div>

<div id="externa-forma"<?php if ($vybranaForma != 'externa') echo ' style="display:none;"'?>>
<p><a href="javascript:hide('externa-forma');show('denna-forma');void(0);">zobraz výsledky pre dennú formu</a></p>
<h2>Externá forma štúdia</h2>
<table>
<tr><td>poradie</td><td>pid</td><td>body</td></tr>
<?php
vypisTabulku($body, 'externa', $vybranaForma, $vybranyKod);
?>
</table>
</div>
<?php
    }
    require 'include/footer.php';
?>
