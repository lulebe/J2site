<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['student']) {
  header("Location: /app/comments.php");
}

$tParams['news'] = $db->getNews();
$tParams['topnews'] = $tParams['news'][0];



//render template
$template = $twig->loadTemplate("home.phtml");
$template->display($tParams);
?>