<?php
//init the app
$secure = true;
require '../init.inc.php';

//teacher?
if ($_SESSION['teacher'] && !isset($_GET['id'])) {
  header("Location: /app/teachersettings.php");
}



$profileid = isset($_GET['id']) ? $_GET['id'] : $_SESSION['userid'];
$tParams['editable'] = $_SESSION['admin'] || $profileid === $_SESSION['userid'];


//profile info changed
require '../imgupload.inc.php';
if ($tParams['editable'] && isset($_POST['edited'])) {
  if ($_POST['pw'] != NULL) {
    $db->setPw($profileid, $_POST['pw']);
  }
  if ($_POST['email'] != NULL) {
    $db->setMail($profileid, $_POST['email']);
  }
  if ($_FILES['profilepic']['size'] > 0) {
    $path = $_SERVER['DOCUMENT_ROOT']."/images/profiles/";
    $imgName = uploadImg($_FILES['profilepic'], $path);
    if ($imgName) {
      $thisuser = $db->getUser($profileid);
      unlink($path . $thisuser['img']);
      $db->setProfilePic($profileid, $imgName);
    }
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
  $type = $_SESSION['teacher'] ? 10 : 0;
  $db->comment($profileid, $text, $userid, $type);
}
//save comment
if ($_POST['editcomment']) {
  $db->deleteComment($_POST['editcomment']);
}



if ($_SESSION['admin']) {
  $tParams['logins'] = $db->getLogins($profileid);
}
$tParams['profile'] = $db->getProfile($profileid);
$tParams['cancomment'] = !$tParams['editable'] || $_SESSION['admin'];
$tParams['canteachercomment'] = $_SESSION['teacher'] || $_SESSION['admin'];

//render template
$template = $twig->loadTemplate("profile.phtml");
$template->display($tParams);
?>