<?php header('Content-type: text/html; charset=UTF-8');?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?=MAINTENANCE?></title>
    </head>
    <body>


    </body>
</html>
<?php
include_once('Util.class.php');
include_once('Database.class.php');
include_once('Magazine.class.php');

Magazine::viderDB();
Magazine::updateList();
?>
