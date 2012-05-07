<?php
$url = 'http://svt.fmph.uniba.sk/?id=';

function generateLatexHeader() {
	$result = '\documentclass[16pt]{minimal}'."\n".
'\usepackage[papersize={500mm,300mm},top=0cm,bottom=0cm,left=-7.5mm,right=0cm,nohead,nofoot]{geometry}'."\n".
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
'\tabcolsep=0pt'."\n".
'\arrayrulewidth=1pt'."\n".
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
'\begin{tabular}{|M|M|M|M|}'. "\n";
		$result = $result . '\hline' . "\n";
	return $result;
}

function generateLatexFooter() {
	$result = '
\end{tabular}

\end{document}';
	return $result;
}

$counter = 0;

function generateLatexQrBarcode($payload) {
	global $counter, $url;

	$result = $result .
'	\begin{tabular}{BBB}'."\n".
'	\begin{pspicture}'."\n".
'	\psbarcode{'."$url"."$payload".'}{height=0.8 width=0.8}{qrcode}'."\n".
'	\end{pspicture} &'."\n".
'		\begin{tabular}{>{\bfseries}c}'."\n".
'		\begin{pspicture}'."\n".
'		\psbarcode{'."$payload".'}{height=0.5}{interleaved2of5}'."\n".
'		\end{pspicture} \\\\'."\n".
'		'.substr($payload,0,4).' - '.substr($payload,4,4).' - '.substr($payload,8,4).' - '.substr($payload,12,4) ."\n".
'		\end{tabular} &'."\n".
'	\begin{pspicture}'."\n".
'	\includegraphics[width=0.8in]{praflogocol}'."\n".
'	\end{pspicture}'."\n".
'	\end{tabular} '
	;
	$counter = ($counter + 1) % 4;
	if ($counter === 0) {
		$result = $result . '\\\\' . "\n";
		$result = $result . '\hline' . "\n";
	} else {
		$result = $result . '&' . "\n";
	}
	return $result;
} 
$f = fopen('php://stdin', 'r');
echo generateLatexHeader();
while ($payload = fgets($f)) {
	echo generateLatexQrBarcode(trim($payload));
}
echo generateLatexFooter();
