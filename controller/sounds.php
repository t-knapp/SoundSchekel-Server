<?php

include 'func/sqlite.php';
include 'func/ffmpeg.php';

$db = DB::getInstance();

$arSounds = $db->findAll();

$action = $_GET['sounds'];

$dirMP3 = 'sound/';

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
    //$dirMP3 = 'sound/';
    $target_path = $dirMP3 . "_tmp"; 
    if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        $length = getDuration($target_path);

        //Normalize
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
    if(!metadata($target_path, $key, $title, $dirMP3 . $lastId)){
        $db->delete($lastId);
        die('Can not rename file.');
    }

    echo "LastInsertId: $lastId" . PHP_EOL;

} else if($action === 'delete'){
    echo '<h4>Delete</h4>';

    $sid = $_GET['sid'];
    echo '<h5>' .$sid . '</h5>';
    
    $db->delete($sid);
    
    if(file_exists($dirMP3 . $sid)){
        if(unlink($dirMP3 . $sid))
            echo "Datei entfernt.";
        else 
            echo "Datei kann nicht entfernt werden.";
    } else {
        echo "Datei existiert nicht mehr.";
    }
?>
<?php
} else {
?>
<table>
    <tr><th>sid</th><th>seq</th><th>key</th><th>title</th><th>length</th><th></th></tr>
    <?php
    foreach($arSounds as $s){
        echo "<tr><td>{$s['sid']}</td><td>{$s['seq']}</td><td>{$s['key']}</td><td>{$s['title']}</td><td>{$s['length']}</td><td><a href=\"?sounds=delete&sid={$s['sid']}\">DEL</a></td></tr>" . PHP_EOL;
    }
    ?>
</table>
<?php } ?>
