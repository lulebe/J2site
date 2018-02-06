<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}
$tParams['umfragen'] = array(1,2,3,4,6,7,8,9,10,12,13,14,15,16,18,19,20,21,22,23,24,26,27,28,29,30,31,32,33,35,37,40,41,42,43,44,45,46,48,50,52,53,54,55,56,57,59,61,62,64,65,66,68);






//render template
$template = $twig->loadTemplate("umfragen.phtml");
$template->display($tParams);
?>