#!/usr/bin/php

<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logger.php';

$db = connect_db();
$logger = new Logger($db);

$query = $db->query('SELECT * from Students WHERE printed = 0 AND pid is not null ORDER BY pid');

$students = $query->fetchAll(PDO::FETCH_ASSOC);
$pids = '';
foreach ($students as $row) {
    $pid = $row['pid'];
    $pids .= $pid . ' ';
}

$db->exec('UPDATE Students SET printed = 1 WHERE printed = 0 AND pid is not null');

$logger->writeToLog('print', 'first pages', null, null, 'administrator', $pids);

$fileloc = __DIR__ . "/../output/printed.tex";
$handle = fopen($fileloc, 'w');

$doc = '\documentclass[12pt]{book}
\usepackage[slovak]{babel}
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
    foreach ($students as $row) {
        $id = $row['id'];
        $meno = $row['meno'];
        $priezvisko = $row['priezvisko'];
        $dateparts = preg_split("/[-]+/", $row['datum_narodenia']);
        $datum = $dateparts[2] . '.' . $dateparts[1] . '.' . $dateparts[0];
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