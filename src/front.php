<?php
if ($config->demo_mode) {
  $config->demo_pid = $pid_checker->generateDemoPid();
  // TODO if demo data will be saved, we might want to check pid uniqueness
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Language" content="sk">
<title><?php print htmlspecialchars($exam->getTitle()); ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" type="text/css" href="base.css">
<link rel="stylesheet" type="text/css" href="style.css">
<!-- TODO shortcut icon? -->
</head>
<body>
<noscript>
<p>Na zobrazenie tohto obsahu je potrebný zapnutý JavaScript.</p>
</noscript>
<script type="text/javascript">
Tester = {};
Tester.config = <?php print json_encode($config); ?>;
</script>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="main.js"></script>
</body>
</html>
