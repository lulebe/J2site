<?php
//init the app
$secure = true;
require '../init.inc.php';


$tParams['classes'] = $db->getClasses($_SESSION['userid']);


//render template
$template = $twig->loadTemplate("classes.phtml");
$template->display($tParams);
?>