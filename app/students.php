<?php
//init the app
$secure = true;
require '../init.inc.php';


$tParams["students"] = $db->getUsers();

$tParams["lele"] = $_SESSION['lele'];

//render template
$template = $twig->loadTemplate("students.phtml");
$template->display($tParams);
?>