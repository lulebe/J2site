<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['lele']) {
  header("Location: /app/home.php");
}


$tParams['teachers'] = $db->getTeachers();







//render template
$template = $twig->loadTemplate("teacherpwlist.phtml");
$template->display($tParams);
?>