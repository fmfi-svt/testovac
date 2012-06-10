<?php

$documentHeaderNormal = '\documentclass[a4paper,twocolumn,10pt,BCOR10mm,oneside,headsepline]{scrartcl}' . "\n";
$documentHeaderLarge = '\documentclass[a4paper,20pt,BCOR10mm,oneside,headsepline]{scrartcl}' . "\n";
$header = '\usepackage[slovak]{babel}' . "\n" .
        '\usepackage[utf8]{inputenc}' . "\n" .
        '\usepackage{wasysym}% provides \ocircle and \Box' . "\n" .
        '\usepackage{enumitem}% easy control of topsep and leftmargin for lists' . "\n" .
        '\usepackage{color}% used for background color' . "\n" .
        '\usepackage{forloop}% used for \Qrating and \Qlines' . "\n" .
        '\usepackage{ifthen}% used for \Qitem and \QItem' . "\n" .
        '\usepackage{typearea}' . "\n" .
        '\areaset{17cm}{26cm}' . "\n" .
        '\setlength{\topmargin}{-1cm}' . "\n" .
        '\usepackage{scrpage2}' . "\n" .
//        '\hyphenation{Nezodpovedané}' . "\n" .
        '\pagestyle{scrheadings}' . "\n";

        
$header2=        '\ohead{\pagemark}' . "\n" .
        '\chead{}' . "\n" .
        '\cfoot{}' . "\n" .
        '\DeclareUnicodeCharacter{00A0}{~}' . "\n" .
        '\newcommand{\degree}{\ensuremath{^\circ}}' . "\n" .
        '\DeclareUnicodeCharacter{B0}{\degree}' . "\n" .
        '\usepackage{newunicodechar}' . "\n" .
        '\newunicodechar{´}{\'}' . "\n" .
        '\newcommand{\Qq}[1]{\textbf{#1}}' . "\n" .
        '\newcommand{\QO}{$\Box$}' . "\n" .
        '\newcounter{qr}' . "\n" .
        '\newcommand{\Qrating}[1]{\QO\forloop{qr}{1}{\value{qr} < #1}{---\QO}}' . "\n" .
        '\newcommand{\Qline}[1]{\rule{#1}{0.6pt}}' . "\n" .
        '\newcounter{ql}' . "\n" .
        '\newcommand{\Qlines}[1]{\forloop{ql}{0}{\value{ql}<#1}{\vskip0em\Qline{\linewidth}}}' . "\n" .
        '\newenvironment{Qlist}{%' . "\n" .
        '%  \renewcommand{\labelitemi}{\QO}' . "\n" .
        '  \renewcommand{\labelitemi}{}' . "\n" .
        '  \begin{itemize}[leftmargin=1.5em,topsep=-.5em]' . "\n" .
        '}{%' . "\n" .
        '  \end{itemize}' . "\n" .
        '}' . "\n" .
        '\newlength{\qt}' . "\n" .
        '\newcommand{\Qtab}[2]{' . "\n" .
        '  \setlength{\qt}{\linewidth}' . "\n" .
        '  \addtolength{\qt}{-#1}' . "\n" .
        '  \hfill\parbox[t]{\qt}{\raggedright #2}' . "\n" .
        '}' . "\n" .
        '' . "\n" .
        '\newcounter{itemnummer}' . "\n" .
        '\newcommand{\Qitem}[2][]{% #1 optional, #2 notwendig' . "\n" .
        '  \ifthenelse{\equal{#1}{}}{\stepcounter{itemnummer}}{}' . "\n" .
        '  \ifthenelse{\equal{#1}{a}}{\stepcounter{itemnummer}}{}' . "\n" .
        '  \begin{enumerate}[topsep=2pt,leftmargin=2.8em]' . "\n" .
        '  \item[\textbf{\arabic{itemnummer}#1.}] #2' . "\n" .
        '  \end{enumerate}' . "\n" .
        '}' . "\n" .
        '\definecolor{bgodd}{rgb}{0.8,0.8,0.8}' . "\n" .
        '\definecolor{bgeven}{rgb}{0.9,0.9,0.9}' . "\n" .
        '\newcounter{itemoddeven}' . "\n" .
        '\newlength{\gb}' . "\n" .
        '\newcommand{\QItem}[2][]{% #1 optional, #2 notwendig' . "\n" .
        '  \setlength{\gb}{\linewidth}' . "\n" .
        '  \addtolength{\gb}{-5.25pt}' . "\n" .
        '  \ifthenelse{\equal{\value{itemoddeven}}{0}}{%' . "\n" .
        '    \noindent\colorbox{bgeven}{\hskip-3pt\begin{minipage}{\gb}\Qitem[#1]{#2}\end{minipage}}%' . "\n" .
        '    \stepcounter{itemoddeven}%' . "\n" .
        '  }{%' . "\n" .
        '    \noindent\colorbox{bgodd}{\hskip-3pt\begin{minipage}{\gb}\Qitem[#1]{#2}\end{minipage}}%' . "\n" .
        '    \setcounter{itemoddeven}{0}%' . "\n" .
        '  }' . "\n" .
        '}' . "\n" .
        '\begin{document}' . "\n" .
        '' . "\n";
//        '\begin{center}' . "\n" .
//        '  \textbf{\large Otázky}' . "\n" .
 //       '\end{center}\vskip1em' . "\n";

$footer = '\end{document}';

//require 'config.php';

function printexamlarge_cli($uid) {
    global $dbh;

    $sth = $dbh->prepare('SELECT * FROM users WHERE pid = :pid');
    $sth->execute(array(':pid' => $uid));
    $user = $sth->fetchObject();
    if ($user === false) {
        echo 'invalid pid';
        return;
    }
    
    global $exam;
    global $documentHeaderLarge;
    global $header;
    global $header2;
    global $footer;
    $questions = $exam->getUserQuestions($uid);
    $myFile = "aux/" . $uid . ".tex";
    echo 'filename: ' . $myFile . "\n";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $documentHeaderLarge);
    fwrite($fh, $header);
    fwrite($fh, '\ihead{'.$uid.'}' . "\n");
    fwrite($fh, '\ifoot{Podpis:}' . "\n");
    fwrite($fh, $header2);
    foreach ($questions as $question_id => $question) {
        $latexed = preg_replace('#<br\s*/?>#', "", $question['body']);
        $latexed = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed);
        $latexed = str_replace("\n" . '          ', "\n\\\\[10pt]\n", $latexed);
        //            $latexed = str_replace('<br>', "\\\\[10pt]",$question['body']);
        fwrite($fh, '\Qitem{ \Qq{' . $latexed . '}');
        fwrite($fh, '\begin{Qlist}');
        //            print_r($question);
        //            die;
        foreach ($question as $section => $value) {
            if ($section != 'body') {
                $latexed2 = preg_replace('#<br\s*/?>#', "", $value['body']);
                $latexed2 = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed2);
                //                $latexed2 = str_replace('          ', "\n\\\\[10pt]\n",  $latexed2);
                //                $latexed2 = preg_replace('\s+', "\\\\[10pt]\n", $latexed2);
                $latexed2 = preg_replace('#<hr\s*/?>#', " \Qlines{1} ", $latexed2);
                $latexed2 = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $latexed2);
                fwrite($fh, '\item ' . $section . ") " . $latexed2);
            }
        }
        fwrite($fh, '\end{Qlist}');
        fwrite($fh, '}' . "\n");
    }
    fwrite($fh, $footer);
    fclose($fh);
    echo shell_exec("/usr/local/texlive/2011/bin/i386-linux/pdfcslatex -output-directory aux $uid.tex");
    echo shell_exec("mv aux/$uid.pdf exams");
}

function printfinished_cli() {
    global $exam;
    global $documentHeaderNormal;
    global $header;
    global $header2;
    global $footer;
    echo 'beginning' . "\n";
    $users = $exam->getFinishedUsersForPrinting();
    print_r($users);
    //die;
    foreach ($users as $user) {
        echo $user->pid . "\n";
//print_r($users);
        $uid = $user->pid;
        echo $uid . "\n";
        $questions = $exam->getUserQuestions($uid);
        $answers = $exam->getUserAnswers($uid);
        echo 'questions got' . "\n";

        $myFile = "aux/" . $uid . ".tex";
        echo 'filename: ' . $myFile . "\n";
        $fh = fopen($myFile, 'w') or die("can't open file");

        fwrite($fh, $documentHeaderNormal);
        fwrite($fh, $header);
        fwrite($fh, '\ihead{'.$uid.'}' . "\n");
        fwrite($fh, '\ifoot{Podpis:}' . "\n");
        fwrite($fh, $header2);
//  global $dbh, $exam;
//    print_r($questions);
//    die;
        foreach ($questions as $question_id => $question) {
            $latexed = preg_replace('#<br\s*/?>#', "", $question['body']);
            $latexed = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed);
            $latexed = str_replace("\n" . '          ', "\n\\\\[10pt]\n", $latexed);
//            $latexed = str_replace('<br>', "\\\\[10pt]",$question['body']);
            fwrite($fh, '\Qitem{ \Qq{' . $latexed . '}');
            fwrite($fh, '\begin{Qlist}');
//            print_r($question);
//            die;
            foreach ($question as $section => $value) {
                if ($section != 'body') {
                    $latexed2 = preg_replace('#<br\s*/?>#', "", $value['body']);
                    $latexed2 = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed2);
//                $latexed2 = str_replace('          ', "\n\\\\[10pt]\n",  $latexed2);
//                $latexed2 = preg_replace('\s+', "\\\\[10pt]\n", $latexed2);
                    $latexed2 = preg_replace('#<hr\s*/?>#', " \Qlines{1} ", $latexed2);
                    $latexed2 = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $latexed2);
                    $subAnswer = $exam->getSubAnswerUser($answers, $question_id, $section);
                    if ($subAnswer === '') {
                        $subAnswer = 'Nezodpovedané.';
                    }
                    fwrite($fh, '\item ' . $section . ") " . $latexed2 . '\hskip0.5cm' . '\textbf{' . $subAnswer . '}' . '');
                }
            }
            fwrite($fh, '\end{Qlist}');
            fwrite($fh, '}' . "\n");
        }
        fwrite($fh, $footer);
        fclose($fh);
        echo shell_exec("/usr/local/texlive/2011/bin/i386-linux/pdfcslatex -output-directory aux $uid.tex");
        echo shell_exec("mv aux/*.pdf spool");
        $exam->userPrinted($uid);
    }
//  \Qitem{ \Qq{Do you like having an option?}
//  \begin{Qlist}
//  \item Yes, this meets my need for autonomy.
//  \item No, I'd rather have someone else decide for me.
//  \item Not sure.
//  \end{Qlist}
}

function printallexams_cli($uid) {
    global $dbh;

    $sth = $dbh->prepare('SELECT * FROM users WHERE pid = :pid');
    $sth->execute(array(':pid' => $uid));
    $user = $sth->fetchObject();
    if ($user === false) {
        echo 'invalid pid';
        return;
    }
    
    global $exam;
    global $documentHeaderNormal;
    global $header;
    global $header2;
    global $footer;
    $questions = $exam->getUserQuestions($uid);
    $answers = $exam->getUserAnswers($uid);
    $myFile = "aux/" . $uid . ".tex";
    echo 'filename: ' . $myFile . "\n";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $documentHeaderNormal);
    fwrite($fh, $header);
    fwrite($fh, $header2);
    foreach ($questions as $question_id => $question) {
        $latexed = preg_replace('#<br\s*/?>#', "", $question['body']);
        $latexed = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed);
        $latexed = str_replace("\n" . '          ', "\n\\\\[10pt]\n", $latexed);
        //            $latexed = str_replace('<br>', "\\\\[10pt]",$question['body']);
        fwrite($fh, '\Qitem{ \Qq{' . $latexed . '}');
        fwrite($fh, '\begin{Qlist}');
        //            print_r($question);
        //            die;
        foreach ($question as $section => $value) {
            if ($section != 'body') {
                $latexed2 = preg_replace('#<br\s*/?>#', "", $value['body']);
                $latexed2 = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed2);
                //                $latexed2 = str_replace('          ', "\n\\\\[10pt]\n",  $latexed2);
                //                $latexed2 = preg_replace('\s+', "\\\\[10pt]\n", $latexed2);
                $latexed2 = preg_replace('#<hr\s*/?>#', " \Qlines{1} ", $latexed2);
                $latexed2 = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $latexed2);
                $subAnswer = $exam->getCompleteSubAnswerUser($answers, $question_id, $section);
                fwrite($fh, '\item ' . $section . ") " . $latexed2 . '\hskip0.5cm' . '\textbf{' . $subAnswer['correctanswer'] . '} ' . '\textbf{' . $subAnswer['points'].'/'. $subAnswer['nsq']. '}');
            }
        }
        fwrite($fh, '\end{Qlist}');
        fwrite($fh, '}' . "\n");
    }
    fwrite($fh, $footer);
    fclose($fh);
    echo shell_exec("/usr/local/texlive/2011/bin/i386-linux/pdfcslatex -output-directory aux $uid.tex");
    echo shell_exec("mv aux/$uid.pdf exams");
}

function printevaluatedexam_cli($uid) {
    global $dbh;

    $sth = $dbh->prepare('SELECT * FROM users WHERE pid = :pid');
    $sth->execute(array(':pid' => $uid));
    $user = $sth->fetchObject();
    if ($user === false) {
        echo 'invalid pid';
        return;
    }
    
    global $exam;
    global $documentHeaderNormal;
    global $header;
    global $header2;
    global $footer;
    $questions = $exam->getUserQuestions($uid);
    $answers = $exam->getUserAnswers($uid);
    $myFile = "aux/" . $uid . ".tex";
    echo 'filename: ' . $myFile . "\n";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $documentHeaderNormal);
    fwrite($fh, $header);
    fwrite($fh, $header2);
    foreach ($questions as $question_id => $question) {
        $latexed = preg_replace('#<br\s*/?>#', "", $question['body']);
        $latexed = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed);
        $latexed = str_replace("\n" . '          ', "\n\\\\[10pt]\n", $latexed);
        //            $latexed = str_replace('<br>', "\\\\[10pt]",$question['body']);
        fwrite($fh, '\Qitem{ \Qq{' . $latexed . '}');
        fwrite($fh, '\begin{Qlist}');
        //            print_r($question);
        //            die;
        foreach ($question as $section => $value) {
            if ($section != 'body') {
                $latexed2 = preg_replace('#<br\s*/?>#', "", $value['body']);
                $latexed2 = str_replace("\n" . '          ' . "\n" . '          ', "\n\\\\[10pt]\n", $latexed2);
                //                $latexed2 = str_replace('          ', "\n\\\\[10pt]\n",  $latexed2);
                //                $latexed2 = preg_replace('\s+', "\\\\[10pt]\n", $latexed2);
                $latexed2 = preg_replace('#<hr\s*/?>#', " \Qlines{1} ", $latexed2);
                $latexed2 = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $latexed2);
                $subAnswer = $exam->getCompleteSubAnswerUser($answers, $question_id, $section);
                if ($subAnswer['useranswer'] === $subAnswer['correctanswer']) {
                    $points = $subAnswer['points'].'/'. $subAnswer['nsq'];
                } else {
                    $points = 0;
                }
                fwrite($fh, '\item ' . $section . ") " . $latexed2 . '\hskip0.5cm' . ' Odpoveď: ' . '\textbf{' . $subAnswer['useranswer'] . '} Správna odpoveď: \hskip0.5cm' . '\textbf{' . $subAnswer['correctanswer'] . '} ' . 'Max. počet bodov: \textbf{' . $subAnswer['points'].'/'. $subAnswer['nsq']. '}' . ' Získaný počet bodov: \textbf{' . $points . '}');
            }
        }
        fwrite($fh, '\end{Qlist}');
        fwrite($fh, '}' . "\n");
    }
    $totalPoints = round($exam->getUserPoints($answers)/6.0, 3);
    fwrite($fh, 'Maximálny možný počet bodov za test: 2400'."\n");
    fwrite($fh, 'Počet získaných bodov za test: '."$totalPoints\n");
    fwrite($fh, $footer);
    fclose($fh);
    echo shell_exec("/usr/local/texlive/2011/bin/i386-linux/pdfcslatex -output-directory aux $uid.tex");
    echo shell_exec("mv aux/$uid.pdf evaluatedexams");
}

?>