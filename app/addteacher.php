<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}

//insert student into db and display pw
if ($_POST['name']) {
  $tParams['pw'] = $db->insertTeacher($_POST['name']);
  $tParams['newname'] = $_POST['name'];
}







//render template
$template = $twig->loadTemplate("addteacher.phtml");
$template->display($tParams);
?>