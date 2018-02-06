<?php
//init the app
$secure = true;
require '../init.inc.php';

if (!$_SESSION['admin']) {
  header("Location: /app/home.php");
}

if ($_POST['sql'] && $_POST['exec']) {
  $tParams['sqlstmt'] = $_POST['sql'];
  $tParams['sqlresponse'] = $db->execQuery($_POST['sql']);
} elseif ($_POST['sql'] && $_POST['save'] && $_POST['queryname']) {
  $db->addQuery($_POST['queryname'], $_POST['sql']);
}

$tParams['users'] = $db->getUsers();


//queries
if ($_GET['delquery']) {
  $db->deleteQuery($_GET['delquery']);
}
$tParams['queries'] = $db->getQueries();




//news
require '../email.inc.php';
if ($_POST['newshead']) {
  $headline = $_POST['newshead'];
  $content = $_POST['newstext'];
  $sendmail = $_POST['sendmail'] ? true : false;
  if (!$_POST['mailonly']) {
    $db->insertNews($headline, $content);
  }
  if ($_POST['sendmail']) {
    $reply = $_POST['resptome'] ? $_SESSION['userid'] : 0;
    if(sendmail($db, $_POST['newshead'], $_POST['newstext'], $reply)) {
      //email success
      $tParams['mailsuccess'] = true;
    } else {
      //email error
      $tParams['mailerror'] = true;
    }
  }
}







//render template
$template = $twig->loadTemplate("admin.phtml");
$template->display($tParams);
?>