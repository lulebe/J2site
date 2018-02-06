<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['teacher']) {
  header("Location: /app/home.php");
}


if ($_POST['pw']) {
  $db->changeTeacherPw($_POST['pw']);
  $tParams['pwChanged'] = true;
}


//render template
$template = $twig->loadTemplate("teachersettings.phtml");
$template->display($tParams);
?>