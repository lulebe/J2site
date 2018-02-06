<?php
//init the app
$secure = true;
require '../init.inc.php';


if ($_SESSION['student']) {
  $tParams['students'] = $db->myComments(0);
  $tParams['classes'] = $db->myComments(1);
  $tParams['cites'] = $db->myComments(2);
  $tParams['teachers'] = $db->myComments(3);
  $tParams['teachercites'] = $db->myComments(4);
  $tParams['general'] = $db->myComments(5);
} else {
  //teacher
  $tParams['students'] = $db->myComments(10);
  $tParams['classes'] = $db->myComments(11);
  $tParams['cites'] = $db->myComments(12);
}



//render template
$template = $twig->loadTemplate("comments.phtml");
$template->display($tParams);
?>