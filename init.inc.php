<?php
error_reporting (E_ALL);

//disable site
$disabled = false;
if ($disabled) {
  die("<h2>Die Seite wird gewartet, bitte versuche es spaeter erneut!</h2>");
}


//imports
require dirname(__FILE__).'/db.class.php';
//session
session_start();

//db connection
$db = new database();


if ($secure && !$_SESSION['loggedin']) {
  header("Location: /index.php");
}

//template-engine init
require dirname(__FILE__).'/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(dirname(__FILE__).'/views');
$twig = new Twig_Environment($loader/*, array("cache"=>dirname(__FILE__)."/twigcache")*/);

$profilelink = $_SESSION['student'] ? "/app/profile.php" : "/app/teachersettings.php";
$homelink = $_SESSION['student'] ? "/app/home.php" : "/app/comments.php";
$surveys_left = $db->getSurveysLeft();
$tParams = array('admin'=>$_SESSION['admin'], 'student'=>$_SESSION['student'], 'teacher'=>$_SESSION['teacher'], 'username'=>$_SESSION['username'], 'profilelink'=>$profilelink, 'homelink'=>$homelink, 'surveys_left'=>$surveys_left);
?>