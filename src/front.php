<?php
  $client_templates = array();
  foreach (scandir('templates/') as $template) {
    if (substr($template, -5) == '.html') {
      $client_templates[substr($template, 0, -5)] = file_get_contents('templates/'.$template);
    }
  }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Language" content="sk">
<title>Príjmačky</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" type="text/css" href="style.css">
<!-- TODO shortcut icon? -->
</head>
<body>
<noscript>
<p>Na zobrazenie tohto obsahu je potrebný JavaScript.</p>
</noscript>
<script type="text/javascript">
DemoMode = <?php print json_encode($config->demo_mode); ?>;
Templates = <?php print json_encode($client_templates); ?>;
</script>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="main.js"></script>
<div id="content"></div>
</body>
</html>
