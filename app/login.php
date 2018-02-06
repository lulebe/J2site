<?php
//init the app
$secure = false;
require '../init.inc.php';

$tParams = array();

function login ($result) {
  if ($result['student']) {
    $_SESSION['loggedin'] = true;
    $_SESSION['userid'] = $result['id'];
    $_SESSION['student'] = true;
    $_SESSION['admin'] = $result['admin'];
    $_SESSION['lele'] = $result['name'] == "Leander Berg";
    $_SESSION['username'] = $result['name'];
    if ($result['mail'] != NULL && $result['dob'] != NULL) {
      header("Location: /app/home.php");
    }
  } elseif ($result['teacher']) {
    $_SESSION['loggedin'] = true;
    $_SESSION['userid'] = $result['id'];
    $_SESSION['teacher'] = true;
    $_SESSION['username'] = $result['name'];
    header("Location: /app/comments.php");
  } else {
    $_SESSION['loggedin'] = false;
    header("Location: /index.php?badlogin");
  }
}

if ($_POST['name'] && $_POST['pw']) {
  login($db->login($_POST['name'], $_POST['pw']));
} elseif ($_SESSION['loggedin'] && $_SESSION['student']) {
  login($db->getUser($_SESSION['userid']));
} elseif ($_SESSION['loggedin'] && $_SESSION['teacher']) {
  login($db->getTeacher($_SESSION['userid']));
} else {
  header("Location: /index.php?badlogin");
}

if ($_POST['email'] && is_numeric($_POST['day']) && is_numeric($_POST['month']) && is_numeric($_POST['year'])) {
  $d = $_POST['day']; $m = $_POST['month']; $y = $_POST['year'];
  if ($d>0&&$d<32 && $m>0&&$m<13 && $y>1994&&$y<1999) {
    $db->setMail($_SESSION['userid'], $_POST['email']);
    $db->setDob($_SESSION['userid'], "$y-$m-$d");
    if ($_POST['newpw']) {
      $db->setPw($_SESSION['userid'], $_POST['newpw']);
    }
    header("Location: /app/home.php");
  }
}

//render template
$template = $twig->loadTemplate("firstsettings.phtml");
$template->display($tParams);
?>