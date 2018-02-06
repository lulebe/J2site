<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}

//insert student into db and display pw
if ($_POST['name']) {
  $db->addSurvey($_POST['name']);
  $tParams['created'] = true;
}







//render template
$template = $twig->loadTemplate("addsurvey.phtml");
$template->display($tParams);
?>