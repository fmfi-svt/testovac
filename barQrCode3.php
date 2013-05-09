<?php
$url = 'http://prijimacky.flaw.uniba.sk/vysledky/';

function generateLatexHeader() {
	return
	'\documentclass[16pt]{minimal}'."\n".
	'\usepackage[papersize={210mm,297mm},top=3mm,bottom=3mm,left=3mm,right=3mm,nohead,nofoot]{geometry}'."\n".
	'\usepackage{tikz}'."\n".
	'\usepackage{tabls}'."\n".
	'\usepackage{verbatim}'."\n".
	'\usepackage{array,amsmath,longtable,ragged2e,pstricks,varwidth}'."\n".
	'\usepackage{pst-barcode}'."\n".
	'\usepackage{auto-pst-pdf}'."\n".
	'\usepackage{eqparbox}'."\n".
	''."\n".
	'\renewcommand{\familydefault}{pcr}'."\n".
	''."\n".
	'\begin{document}'."\n".
	'\newsavebox\TBox'."\n".
	'\tabcolsep=0.25mm'."\n".
	'\arrayrulewidth=0.25mm'."\n".
	'\newenvironment{saveTBox}'."\n".
	'  {\begin{lrbox}{\TBox}\varwidth{\linewidth}}'."\n".
	'  {\endvarwidth\end{lrbox}%'."\n".
	'   \fboxrule=0pt\fboxsep=5pt\fbox{\usebox\TBox}}'."\n".
	''."\n".
	'\newcolumntype{B}{@{}>{\saveTBox}c<{\endsaveTBox}@{}}'."\n".
	'\newcolumntype{M}{>{\centering\arraybackslash}c}'."\n".
	''."\n".
	'\def\padded#1#2{%'."\n".
	'   \setbox0\hbox{#2}%'."\n".
	'   \dimen0=\dp0'."\n".
	'   \setbox2\hbox{\hskip #1\vbox{\vskip #1\box0\vskip#1}\hskip#1}%'."\n".
	'   \advance\dimen0 by #1%'."\n".
	'   \leavevmode\lower\dimen0\box2}'."\n".
	''."\n".
	'\setlength\LTpre{0mm}'."\n".
	'\setlength\LTpost{0mm}'."\n".
	''."\n".
	'\begin{longtable}{MMM}'."\n".
	'\vspace{0mm}'."\n";
}

function generateLatexFooter() {
	return
	'\end{longtable}'."\n".
	'\end{document}';
}

$counter = 0;

function generateLatexQrBarcode($payload) {
	global $counter, $url;

	$result =
'   \begin{tabular}{B}'."\n".
'     \begin{tabular}{>{\bfseries}c}'."\n".
'       \begin{pspicture}'."\n".
'         \psbarcode{'."$payload".'}{height=0.5}{code128}'."\n".
'       \end{pspicture}'. "\n".
'     \begin{pspicture}'."\n".
'       \includegraphics[height=0.5in,width=0.5in]{praflogogray}'."\n".
'     \end{pspicture}'."\n".

 '\\\\'."\n".
'       '.substr($payload,0,4).'\\,-\\,'.substr($payload,4,4).'\\,-\\,'.substr($payload,8,4).'\\,-\\,'.substr($payload,12,4)."\n".
'     \end{tabular} '."\n".
'   \end{tabular} ';

	$counter = ($counter + 1) % 3;
	if ($counter === 0) {
		$result = $result . "\n" . '\\\\' . "\n";
		$result = $result . '\vspace{\arrayrulewidth}' . "\n";
	} else {
		$result = $result . "\n" . '&' . "\n";
	}
	return $result;
} 
$f = fopen('php://stdin', 'r');
echo generateLatexHeader();
while ($payload = fgets($f)) {
	echo generateLatexQrBarcode(trim($payload));
}
echo generateLatexFooter();
