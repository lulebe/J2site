<?php
  //provide $_FILES[xx] and path with slashes e.g. "/images/"
  //returns filename based on timestamp and random value
  function uploadImg($file, $targetPath) {
    if ($file['size'] > 1048576 || $file['type'] != "image/jpeg" || !getimagesize($file["tmp_name"])) {
      return false; //filesize too high or file is not a jpg
    } else {
      //not very secure but yolo :D
      $filename;
      $targetFile;
      do {
        $filename = time() . mt_rand(0,999) . ".jpg";
        $targetFile = $targetPath . $filename;
      } while (file_exists($targetFile));
      if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $filename;
      } else {
        return false;
      }
    }
  }
?>