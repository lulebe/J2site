<?php
//init the app
$secure = true;
require '../init.inc.php';

if ($_SESSION['student']) {
  //mark as favourite
  if (isset($_GET['myfav'])) {
    $db->setFavClass($_GET['id']);
  }

  //add membership
  if (isset($_GET['addmember'])) {
    $db->addClassMember($_GET['id'], $_SESSION['userid']);
  }
  //remove membership
  if (isset($_GET['rmmember'])) {
    $db->rmClassMember($_GET['id'], $_SESSION['userid']);
  }
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
  $type = $_SESSION['teacher'] ? 11 : 1;
  $db->comment($_GET['id'], $text, $userid, $type);
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
//new cite?
if (isset($_POST['cite']) && $_POST['source'] != NULL) {
  $text = $_POST['cite'];
  $source = $_POST['source'];
  $userid = $_SESSION['userid'];
  if ($_POST['editcite']) {
    $ecmt = $db->getComment($_POST['editcite']);
    $userid = $ecmt['userid'];
  }
  $type = $_SESSION['teacher'] ? 12 : 2;
  $db->comment($_GET['id'], $text, $userid, $type, $source);
}
//save cite
if ($_POST['editcite']) {
  $db->deleteComment($_POST['editcite']);
}


//change teacher
if ($_POST['teacherid'] && $_SESSION['admin']) {
  $db->setClassTeacher($_GET['id'], $_POST['teacherid']);
}



$tParams['class'] = $db->getClass($_GET['id']);
$tParams['ismember'] = $db->isClassMember($_GET['id'], $_SESSION['userid']);

if ($_SESSION['admin']) {
  $tParams['teachers'] = $db->getTeachers();
}

$tParams['canteachercomment'] = ($_SESSION['admin'] || $_SESSION['teacher']) ? true : false;


//render template
$template = $twig->loadTemplate("class.phtml");
$template->display($tParams);
?>