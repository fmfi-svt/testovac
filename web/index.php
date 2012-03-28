<?php

ini_set('display_errors', FALSE);
chdir('..');
require 'config.php';

require 'src/util.php';

if (!isset($_POST['action'])) {
  require 'src/front.php';
}
else if ($_POST['action'] === 'login') {
  require 'src/login.php';
  login_jsonapi();
}
else if ($_POST['action'] === 'save') {
  require 'src/update.php';
  save_jsonapi();
}
else if ($_POST['action'] === 'close') {
  require 'src/update.php';
  close_jsonapi();
}
else {
  json_out(array('error' => 'unknown action'));
}
