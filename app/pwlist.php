<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['lele']) {
  header("Location: /app/home.php");
}


$tParams['user'] = $db->getUsers();







//render template
$template = $twig->loadTemplate("pwlist.phtml");
$template->display($tParams);
?>