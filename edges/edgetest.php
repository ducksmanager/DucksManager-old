<?php
include_once('../JS.class.php');
include_once('Edge.class.php');
?>
<html>
    <head>
        <?php
        new JS('../js/scriptaculous/lib/prototype.js');
        new JS('../js/scriptaculous/src/scriptaculous.js');
        new JS('../js/edges.js');
        ?>

    </head>
    <body id="body" style="margin:0;padding:0" style="white-space:nowrap;">
        <?php
        echo getImgHTMLOf('fr', 'SPG', 10);
        for ($i=88;$i<=150;$i++)
            echo getImgHTMLOf('fr', 'SPG', $i);/*
        ?>
    </body>
</html>