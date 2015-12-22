<!DOCTYPE html>
<body>
  <head>
    <meta charset="utf-8"/>
  </head>
  <body>
<?php


error_reporting(E_ALL);
ini_set("display_errors", 1);


include 'sqlite.php';
include 'ffmpeg.php';

if(!isset($_POST['key'])   || empty($_POST['key']) ||
   !isset($_POST['title']) || empty($_POST['title']) 
   //|| !isset($_POST['file']) || empty($_POST['file'])
){
    die("Nothing");
}

//File
$dirMP3 = 'sound/';
$target_path = $dirMP3 . "_tmp"; 
if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
    //die('Okay');
    //$mp3file = new MP3File($target_path);
    //$duration = $mp3file->getDuration();
    $length = getDuration($target_path);

    //Normalize and rename
    normalize($target_path);
    rename($target_path . ".mp3", $target_path);
} else {
    die('Upload Failed');
}

// If Upload ok, save to DB

$db     = DB::getInstance();

$seq    = $db->getNextSeq();
$key    = $_POST['key'];
$title  = $_POST['title'];
//$length = "xx:xx";

$lastId = $db->insertSound($seq, $key, $title, $length);

//Delete from DB if file problem
if(!rename($target_path, $dirMP3 . $lastId)){
    $db->delete($lastId);
    die('Can not rename file.');
}

echo '<a href="form.php">Zurück</a>';

echo "<pre>"; print_r($db->findAll());  echo"</pre>";

?>
  <body>
</html>
