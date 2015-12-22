<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->
  <script>
  $(function() {
    var availableTags = [
      <?php
        include 'sqlite.php';
        $db = DB::getInstance();
        echo '"' . implode("\",\"", $db->getCategories()) . '"';
      ?>
    ];
    $( "#tags" ).autocomplete({
      source: availableTags,
      minLength: 0
    });
  });
  </script>
</head>
<body>
    <form method="POST" enctype="multipart/form-data" action="upload.php" accept-charset="UTF-8">
        <table>
            <tr><td>Datei:</td><td><input type="file" name="file" accept=".mp3"></td></tr>
            <tr><td>Kategorie:</td><td><input type="text" id="tags" name="key" onfocus="javascript:$(this).autocomplete('search','');"></td></tr>
            <tr><td>Titel:</td><td><input type="text" name="title"></td></tr>
            <tr><td></td><td><input type="submit" value="Upload"></td></tr>
        </table>
    </form>
</body>
</html>
