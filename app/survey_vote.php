<?php
//init the app
echo 1;
$secure = true;
require '../init.inc.php';

if ($_SESSION['student'] && $_GET['ans']) {
  $db->vote($_GET['id'], $_GET['ans'], $_SESSION['userid']);
}
?>