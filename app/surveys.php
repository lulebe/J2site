<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['student']) {
  header("Location: /app/comments.php");
}

$tParams['surveys'] = $db->getSurveys($_SESSION['userid']);


//render template
$template = $twig->loadTemplate("surveys.phtml");
$template->display($tParams);
?>