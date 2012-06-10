#!/usr/bin/php

<?php

require_once 'actions.php';


$db = new Repository();


$query = $db->printStudents();


$linefeed = "\n";
$fileloc = "logs/printed.tex";
$handle = fopen($fileloc, 'w');

$doc = '\documentclass[12pt]{book}
\usepackage{slovak}
\usepackage{tabularx}
\usepackage{amsfonts}
\usepackage{amssymb}
\usepackage{epsfig}
\usepackage{color}
\usepackage{fullpage}
\usepackage{array}
\usepackage{enumerate}
\usepackage[utf8]{inputenc}
\newcommand{\Qline}[1]{\rule{#1}{0.6pt}}

\begin{document} ';

print_r("Vytvaram pdf-ka pre studentov, ktori maju pid a este nie su vytlaceni." . "\n");

try {
    while ($query->fetchInto($row)) {
	
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
    	print_r('Tlacim studenta s pid: ' . $pid . "\n");
	$doc = $doc . '
\newpage
\thispagestyle{empty}
\noindent
\begin{minipage}{0.2\textwidth}
\includegraphics[width=\textwidth]{praflogocol}\\\\[-\baselineskip]
\end{minipage}
\hfill
\begin{minipage}{0.7\textwidth}
\LARGE
Univerzita Komenského v Bratislave\\\\
Právnická fakulta
\end{minipage}

\vspace{0.5in}

\begin{center}
\Large{Odpoveďový hárok}
\end{center}
\vspace{0.5in}

Svojím podpisom vyjadrujem správnosť uloženia mojich odpovedí. Ďalej svojím podpisom
vyjadrujem súhlas s tým, aby výsledok prijímacej skúšky bol zverejnený na vývesnej tabuli v priestoroch UK, 
Právnickej fakulty a na webových 
stránkach UK a Právnickej fakulty. Zároveň týmto dávam Univerzite Komenského
svoj súhlas na spracovanie mojich osobných údajov v zmysle zákona o ochrane osobných údajov pre účely prijímacieho konania.

\vspace{0.5in}
\begin{description}
\item[ID uchádzača:] ' . $pid . '
\item[Forma štúdia:] ' . $forma . '
\item[Meno a priezvisko:] ' . $meno . ' ' . $priezvisko . '
\item[Dátum narodenia uchádzača:] ' . $datum . '
\item[V Bratislave dňa:] ' . date("d.m.Y") . '
\end{description}
\vspace{0.5in}
\begin{description}
\item[Podpis uchádzača:] \hskip0.5cm \Qline{3cm}
\end{description} ';    
    }
} catch (Exception $e) {
    throw $e;
}

$doc = $doc . '\end{document} ';

fputs($handle, $doc);

fclose($handle);
?>
