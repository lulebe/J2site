<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['student'])
  header("Location: /app/comments.php");



if ($_GET['ans'] && !$_GET['merge'])
  $db->vote($_GET['id'], $_GET['ans'], $_SESSION['userid']);

if ($_GET['delans'] && $_SESSION['admin'])
  $db->deleteAnswer($_GET['delans']);

if ($_GET['ans'] && $_GET['merge'] && $_SESSION['admin'])
  $db->mergeSurveyAns($_GET['merge'], $_GET['ans']);

if (isset($_GET['delsurvey']) && $_SESSION['admin']) {
  $db->deleteSurvey($_GET['id']);
  header("Location: /app/surveys.php");
}

$tParams['survey'] = $db->getSurvey($_GET['id'], $_SESSION['userid']);

$lttrs = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$colors = array('');
$tParams['percentage'] = 0;
foreach ($tParams['survey']['answers'] as $key=>$el) {
  $el['percentage'] = round($el['count']/$tParams['survey']['votes'] * 100,1);
  $tParams['percentage'] += $el['percentage'];
  $el['width'] = $el['count']/$tParams['survey']['votes'] * 100 - 0.02;
  if ($el['width'] < 0) $el['width'] = 0;
  $el['letter'] = ($key < 26) ? $lttrs[$key] : $key+1;
  $el['color'] = substr(md5(rand()), 0, 6);
  $tParams['survey']['answers'][$key] = $el;
}

//render template
$template = $twig->loadTemplate("survey.phtml");
$template->display($tParams);
?>