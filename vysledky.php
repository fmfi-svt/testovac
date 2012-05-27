<?php
$page = 'vysledky';
require 'include/header.php';
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
	$vybraneBody = -1;
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
		if (!empty($body['denna'][$kod])) $body['denna'][$kod] = round($body['denna'][$kod]+($zlomok[0]/$zlomok[1]),2);
		else if (!empty($body['externa'][$kod])) $body['externa'][$kod] = round($body['externa'][$kod]+($zlomok[0]/$zlomok[1]),2);
		else throw new Exception('Nenašli sa body z vysvedčenia pre jeden z kódov.');
	}

	arsort($body['denna']);
	arsort($body['externa']);

        if (isset($body[$vybranaForma][$vybranyKod])) $vybraneBody = $body[$vybranaForma][$vybranyKod];
?>
<script>
	function show(id) {
		document.getElementById(id).style.display='block';
	}
	function hide(id) {
		document.getElementById(id).style.display='none';
	}
</script>
<div id="denna-forma"<?php if ($vybranaForma != 'denna') echo ' style="display:none;"'?>>
<p><a href="javascript:hide('denna-forma');show('externa-forma');void(0);">zobraz výsledky pre externú formu</a></p>
<h2>Výsledková listina pre dennú formu štúdia</h2>
<table>
<tr><td>poradie</td><td>body</td></tr>
<?php
	$poradie = 0;
	$posledne = -1;
	foreach ($body['denna'] as $skore) {
		if ($skore != $posledne) $poradie++;
		$posledne = $skore;
		if ($vybranaForma == 'denna' && $vybraneBody == $skore) {
			echo '<tr class="selected-score">';
			$vybraneBody = -1;
		}
		else echo "<tr>";
		echo "<td>$poradie.</td><td>$skore</td></tr>";
	}
	
?>
</table>
</div>

<div id="externa-forma"<?php if ($vybranaForma != 'externa') echo ' style="display:none;"'?>>
<p><a href="javascript:hide('externa-forma');show('denna-forma');void(0);">zobraz výsledky pre dennú formu</a></p>
<h2>Výsledková listina pre externú formu štúdia</h2>
<table>
<tr><td>poradie</td><td>body</td></tr>
<?php
	$poradie = 0;
	$posledne = -1;
	foreach ($body['externa'] as $skore) {
		if ($skore != $posledne) $poradie++;
		$posledne = $skore;
		if ($vybranaForma == 'externa' && $vybraneBody == $skore) {
			echo '<tr class="selected-score">';
			$vybraneBody = -1;
		}		else echo "<tr>";
		echo "<td>$poradie.</td><td>$skore</td></tr>";
	}
	
?>
</table>
</div>
<?php
    }
    require 'include/footer.php';
?>