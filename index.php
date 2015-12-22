<?php include 'func/userauth.php';

$auth->cookielogin();

if (isset($_GET['logout'])) {
    $auth->logout();
} else if (isset($_GET['sounds'])) {
    $action = 'sounds';
} else {
    unset($action);
}
if (isset($_POST['user'])) {
    $authOk = ($auth->login($_POST['user'], $_POST['pass'], true));
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
        <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <title>Interner Bereich</title>
        <style>
            #main_nav ul {
                list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                background-color: #333;
            }

            #main_nav a {
                display: block;
                text-decoration: none;
                padding: 5px 15px;
                color: #000;
            }
                        
            #main_nav li {
                float: left;
            }

            #main_nav li a {
                display: block;
                color: white;
                text-align: center;
                padding: 14px 16px;
                text-decoration: none;
            }

            #main_nav li p {
                display: block;
                color: white;
                text-align: center;
                padding: 14px 16px;
                text-decoration: none;
                margin: 0px;
            }

            #main_nav li a:hover:not(.active) {
                background-color: #111;
            }

            #main_nav .active {
                background-color: #4CAF50;
            }
        </style>
    </head>
    <body>
        <?php if($auth->isAuthenticated()) { ?>
        <nav id="main_nav">
        <ul>
          <li><p class="active">Hallo <?php echo $auth->uname(); ?></p></li>
          <li><a href="?sounds">Sounds</a>
              <ul>
                  <li><a href="?sounds=add">Hinzufügen</a></li>
              </ul>
          </li>
          <li><a href="?versions">App-Versionen</a></li>
          <ul style="float:right;list-style-type:none;">
            <li><a href="?benutzer">Benutzer</a></li>
            <li><a href="?konto">Konto</a></li>
            <li><a href="?logout">Logout</a></li>
          </ul>
        </ul>
        </nav>
        <?php } else { ?>
        <form method="POST" action="index.php">
        <table>
            <tr><td>Username:</td><td><input name="user" /></td></tr>
            <tr><td>Password:</td><td><input type="password" name="pass" /></td></tr> 
            <tr><td></td><td><input type="submit" value="Login" /></td></tr>
        </table>
        </form>
        <?php } ?>
        <!-- Content Start -->
        <?php 
        if(isset($action)){
            include "controller/{$action}.php";
        }
        ?>
        <!-- Content End -->
    </body>
</html>
