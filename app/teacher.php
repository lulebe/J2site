<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['student']) {
  header("Location: /app/comments.php");
}

//change name
if ($_POST['name']) {
  $db->changeTeacherName($_GET['id'], $_POST['name']);
}
//change pw
if ($_POST['pw'] && $_SESSION['admin']) {
  $db->changeTeacherPw($_POST['pw'], $_GET['id']);
}


//delete comment
if ($_GET['deletecomment']) {
  $db->deleteComment($_GET['deletecomment']);
}
//edit comment
if ($_GET['editcomment']) {
  $tParams['editcomment'] = $db->getComment($_GET['editcomment']);
}
//new comment?
if (isset($_POST['comment'])) {
  $text = $_POST['comment'];
  $userid = $_SESSION['userid'];
  if ($_POST['editcomment']) {
    $ecmt = $db->getComment($_POST['editcomment']);
    $userid = $ecmt['userid'];
  }
  $db->comment($_GET['id'], $text, $userid, 3);
}
//save comment
if ($_POST['editcomment']) {
  $db->deleteComment($_POST['editcomment']);
}


//delete cite
if ($_GET['deletecite']) {
  $db->deleteComment($_GET['deletecite']);
}
//edit cite
if ($_GET['editcite']) {
  $tParams['editcite'] = $db->getComment($_GET['editcite']);
}
//new comment?
if (isset($_POST['cite']) && $_POST['source'] != NULL) {
  $text = $_POST['cite'];
  $source = $_POST['source'];
  $userid = $_SESSION['userid'];
  if ($_POST['editcite']) {
    $ecmt = $db->getComment($_POST['editcite']);
    $userid = $ecmt['userid'];
  }
  $db->comment($_GET['id'], $text, $userid, 4, $source);
}
//save cite
if ($_POST['editcite']) {
  $db->deleteComment($_POST['editcite']);
}



$tParams['teacher'] = $db->getTeacherProfile($_GET['id'], $_SESSION['admin']);


//render template
$template = $twig->loadTemplate("teacher.phtml");
$template->display($tParams);
?>