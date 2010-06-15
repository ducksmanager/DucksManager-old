<?php
include_once('JS.class.php');
include_once('Etagere.class.php');
include_once('Edge.class.php');
include_once('Database.class.php');
@session_start();
$d=new Database();
$id_user=$d->user_to_id($_SESSION['user']);
$l=$d->toList($id_user);


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
        Edge::$grossissement=1.5;
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
        foreach($l->collection as $pays=>$magazines) {
            foreach($magazines as $magazine=>$numeros) {
				sort($numeros);
				foreach($numeros as $numero) {
                    echo getImgHTMLOf($pays, $magazine, $numero[0]);
                }
            }
        }
        echo Edge::getEtagereHTML(false);
        ?>
    <div id="largeur_etagere" style="display:none" name="<?=Etagere::$largeur?>"></div>
    </body>
</html>