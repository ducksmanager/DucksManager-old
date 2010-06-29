<?php
include_once('JS.class.php');
include_once('Etagere.class.php');
include_once('Edge.class.php');


$largeur=$_POST['largeur'];
$hauteur=$_POST['hauteur'];
?>
<html>
    <head>
        <?php
        new JS('js/scriptaculous/lib/prototype.js');
        new JS('js/scriptaculous/src/scriptaculous.js');
        new JS('js/edges.js');
        ?>

    </head>
    <body id="body" style="margin:0;padding:0" style="white-space:nowrap;">
        <?php
        Edge::$grossissement=$_POST['grossissement'];
        Etagere::$largeur=$largeur;
        Etagere::$hauteur=$hauteur;
        Etagere::$epaisseur=20;
        Etagere::$texture1=$_POST['texture1'];
        Etagere::$sous_texture1=$_POST['sous_texture1'];
        Etagere::$texture2=$_POST['texture2'];
        Etagere::$sous_texture2=$_POST['sous_texture2'];
        list($width, $height, $type, $attr)=getimagesize('edges/textures/'.Etagere::$texture1.'/'.Etagere::$sous_texture1.'.jpg');
        if ($width<Etagere::$largeur)
            Etagere::$largeur=$width;
        echo Edge::getEtagereHTML();
        list($html, $pourcentage_visible)=Edge::getHTML(true);
        echo $html;
        echo Edge::getEtagereHTML(false);
        ?>
    <div id="largeur_etagere" style="display:none" name="<?=Etagere::$largeur?>"></div>
    <div id="nb_numeros_visibles" style="display:none" name="<?=$pourcentage_visible?>"></div>
    <div id="hauteur_etage" style="display:none" name="<?=Etagere::$hauteur_max_etage?>"></div>
    <div id="grossissement" style="display:none" name="<?=Edge::$grossissement?>"></div>
    </body>
</html>