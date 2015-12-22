<?php

include 'func/sqlite.php';
include 'func/ffmpeg.php';

$db = DB::getInstance();

$arSounds = $db->findAll();

$action = $_GET['sounds'];

if($action === 'add') {
?>
<script>
  $(function() {
    var availableTags = [
      <?php
        echo '"' . implode("\",\"", $db->getCategories()) . '"';
      ?>
    ];
    $( "#tags" ).autocomplete({
      source: availableTags,
      minLength: 0
    });
  });
  </script>
<table>
    <form method="POST" enctype="multipart/form-data" action="?sounds=upload" accept-charset="UTF-8">
        <table>
            <tr><td>Datei:</td><td><input type="file" name="file" accept=".mp3"></td></tr>
            <tr><td>Kategorie:</td><td><input type="text" id="tags" name="key" onfocus="javascript:$(this).autocomplete('search','');"></td></tr>
            <tr><td>Titel:</td><td><input type="text" name="title"></td></tr>
            <tr><td></td><td><input type="submit" value="Upload"></td></tr>
        </table>
    </form>
</table>
<?php
} else if($action === 'upload') {
    // Upload
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

    $lastId = $db->insertSound($seq, $key, $title, $length);

    //Delete from DB if file problem
    if(!rename($target_path, $dirMP3 . $lastId)){
        $db->delete($lastId);
        die('Can not rename file.');
    }
    
    echo "LastInsertId: $lastId" . PHP_EOL;
?>
<?php
} else {
?>
<table>
    <tr><th>sid</th><th>seq</th><th>key</th><th>title</th><th>length</th><th></th></tr>
    <?php
    foreach($arSounds as $s){
        echo "<tr><td>{$s['sid']}</td><td>{$s['seq']}</td><td>{$s['key']}</td><td>{$s['title']}</td><td>{$s['length']}</td><td>DEL</td></tr>" . PHP_EOL;
    }
    ?>
</table>
<?php } ?>
