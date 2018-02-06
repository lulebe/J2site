<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}

//insert student into db and display pw
if (isset($_POST['name'])) {
  $tParams['newpw'] = $db->insertUser($_POST['name']);
  $tParams['newname'] = $_POST['name'];
}







//render template
$template = $twig->loadTemplate("addstudent.phtml");
$template->display($tParams);
?>