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
        $total_numeros=0;
        $total_numeros_visibles=0;
        foreach($l->collection as $pays=>$magazines) {
            foreach($magazines as $magazine=>$numeros) {
				sort($numeros);
                $total_numeros+=count($numeros);
				foreach($numeros as $numero) {
                    list($texte,$est_visible)=getImgHTMLOf($pays, $magazine, $numero[0]);
                    echo $texte;
                    if ($est_visible===true)
                        $total_numeros_visibles++;
                }
            }
        }
        echo Edge::getEtagereHTML(false);
        ?>
    <div id="largeur_etagere" style="display:none" name="<?=Etagere::$largeur?>"></div>
    <div id="nb_numeros_visibles" style="display:none" name="<?=intval(100*$total_numeros_visibles/$total_numeros)?>"></div>
    <div id="hauteur_etage" style="display:none" name="<?=Etagere::$hauteur_max_etage?>"></div>
    <div id="grossissement" style="display:none" name="<?=Edge::$grossissement?>"></div>
    </body>
</html>