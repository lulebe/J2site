<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['student'])
  header("Location: /app/comments.php");


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
  $type = 5;
  $db->comment(0, $text, $userid, $type);
}
//save comment
if ($_POST['editcomment']) {
  $db->deleteComment($_POST['editcomment']);
}

//get comments
$tParams['comments'] = $db->getOtherComments();

//render template
$template = $twig->loadTemplate("misccomments.phtml");
$template->display($tParams);
?>