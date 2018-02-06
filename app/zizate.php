<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}
$tParams['zitate'] = $db->getAllCites();






//render template
$template = $twig->loadTemplate("zitate.phtml");
$template->display($tParams);
?>