<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['student']) {
  header("Location: /app/comments.php");
}

$tParams['teachers'] = $db->getTeachers();
$tParams["lele"] = $_SESSION['lele'];

//render template
$template = $twig->loadTemplate("teachers.phtml");
$template->display($tParams);
?>