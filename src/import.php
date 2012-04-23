<?php

function import_cli() {
  import_action($_SERVER['argv'][2]);
}

function import_action($filename) {
  global $exam;
  $exam->importQuestions($filename);
}
