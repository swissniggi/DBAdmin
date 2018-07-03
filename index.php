<?php
    require_once 'php/DBAdmin_Controller.php';
    session_start();                                                                    
    $cont = new DBAdmin_Controller();
    $cont->getRequest();
?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="css/DBAdmin_style.css">
        <script type="text/javascript" src="js/DBAdmin_javascript.js"></script>
        <title>DB Admin</title>
    </head>        
    <body>        
    </body>
</html>
