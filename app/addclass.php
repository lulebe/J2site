<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}

//insert student into db and display pw
if ($_POST['name'] && $_POST['teacherid']) {
  $tParams['created'] = $db->insertClass($_POST['name'], $_POST['teacherid']);
  $tParams['newname'] = $_POST['name'];
  $newTeacher = $db->getTeacher($_POST['teacherid']);
  $tParams['newteacher'] = $newTeacher['name'];
}


$tParams['teachers'] = $db->getTeachers();




//render template
$template = $twig->loadTemplate("addclass.phtml");
$template->display($tParams);
?>