<?php
function exportresults_cli() {
    global $exam;
    echo 'beginning' . "\n";
    //ktorych userov? vsetkych? uz printnutych?
    $users = $exam->getFinishedUsers();
    $myFile = "results/hbody.php";
    echo 'filename: ' . $myFile . "\n";
    $fh = fopen($myFile, 'w') or die("can't open file");
    $head = '<?php $bodyH = array('."\n";
    fwrite($fh, $head);

    foreach ($users as $user) {
        $userAnswers = $exam->getUserAnswers($user->pid);
        $vysledneBody = $exam->getUserPoints($userAnswers);
        fwrite($fh, "'$user->pid' => array($vysledneBody, 6),\n");
        print "$user->pid\n";
    }

    $footer = '); ?>';
    fwrite($fh, $footer);
    fclose($fh);
}
//        $bodyH = array(
//    '1234-1234-1234-1234' => array(11, 3),
//    '1234-1234-1234-1235' => array(19, 3),
//    '1234-1234-1234-1236' => array(11, 3),
//    '1334-9534-1434-1234' => array(78, 3),
//    '1334-9534-1434-1235' => array(65, 3),
//);
        // put your code here
?>
