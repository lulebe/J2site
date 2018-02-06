<?php
//init the app
$secure = false;
require 'init.inc.php';

if ($_GET['logout']) {
  session_destroy();
  $_SESSION = array();
}

if ($_SESSION['loggedin']) {
  header('Location: /app/login.php');
}



if (isset($_GET['badlogin'])) {
  $tParams['loginerror'] = true;
} else {
  $tParams['loginerror'] = false;
}

//using ssl?
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
  $tParams['insecure'] = false;
else
  $tParams['insecure'] = true;
      
//render login template
$template = $twig->loadTemplate("index.phtml");
$template->display($tParams);
?>