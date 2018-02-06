<?php
require dirname(__FILE__).'/phpmailer/class.phpmailer.php';
require dirname(__FILE__).'/phpmailer/class.smtp.php';
function sendmail ($db, $subject, $text, $replytouser=0) {
  $mail = new PHPMailer();
  $mail->isSMTP();
  $mail->Host = 'smtp.1blu.de';
  $mail->SMTPAuth = true;
  $mail->Username = 'e182279_0-j2';
  $mail->Password = '0fv9na';
  $mail->SMTPSecure = 'tls';
  $mail->Port = 25;
  
  $mail->From = 'j2@lulebe.net';
  $mail->FromName = 'Abizeitung';
  
  $mail->Subject = $subject;
  $mail->Body = $text;
  
  //reply settings
  if ($replytouser) {
    $myusr = $db->getUser($replytouser);
    $mail->addReplyTo($myusr['mail'], $myusr['name']);
  }
  
  //add recipients
  $adrs = $db->getEmails();
  $noadrs = true;
  foreach ($adrs as $usr) {
    $noadrs = false;
    $mail->addAddress($usr['mail'], $usr['name']);
  }
  if ($noadrs) {
    return false;
  }
  if (!$mail->send()) {
    return false;
  } else {
    return true;
  }
}
?>