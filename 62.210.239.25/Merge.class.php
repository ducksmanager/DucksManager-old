<?php
$dimensions_max=5000*5000;
global $debug;
$debug=isset($_GET['debug']);
$no_database=true;
error_reporting(E_ALL);
require_once('Util.class.php');
if (isset($_GET['user'])) {
    $nom_fichier_data='edges/_tmp/'.$_GET['user'].'.json';
    $nom_image='edges/_tmp/'.$_GET['user'].'.png';
    $contenu=Util::get_page('http://www.ducksmanager.net/'.$nom_fichier_data);
    list($texture1,$texture2,$largeur,$pos)=explode("\n",$contenu);
    $image_texture1=use_or_fetch('http://www.ducksmanager.net/edges/textures/'.$texture1.'.jpg');
    $image_texture2=use_or_fetch('http://www.ducksmanager.net/edges/textures/'.$texture2.'.jpg');
    $pos=json_decode($pos);

    foreach($pos as $type_element=>$pos_elements) {
        foreach($pos_elements as $i=>$pos_element) {
            $pos->$type_element->$i=explode('-',$pos->$type_element->$i);
        }
    }
    $max_y=0;
    $pos_sup_gauche=array();
    foreach($pos->etageres->etageres as $i=>$pos_etagere) {
        $pos_etagere_courante=explode(',',$pos_etagere);
        if ($i==0)
            $pos_sup_gauche=$pos_etagere_courante;
        if ($pos_etagere_courante[1] > $max_y)
            $max_y=$pos_etagere_courante[1];
    }
    $min_y=$pos_sup_gauche[1];
    $hauteur=$max_y-$min_y+16;
    if ($largeur * $hauteur > $dimensions_max) {
        echo 'M&eacute;moire insuffisante ! ';exit(0);
    }
    $im=imagecreatetruecolor($largeur, $hauteur);
    for ($i=0;$i<$largeur;$i+=imagesx($image_texture1))
        for ($j=0;$j<$hauteur;$j+=imagesy($image_texture1))
            imagecopy ($im, $image_texture1, $i, $j, 0, 0, imagesx($image_texture1), imagesy($image_texture1));

    imagedestroy($image_texture1);
    foreach($pos->etageres->etageres as $i=>$pos_etagere) {
        $pos_etagere_courante=explode(',',$pos_etagere);
        imagecopyresampled($im, $image_texture2, 0, $pos_etagere_courante[1]-$pos_sup_gauche[1], 0, 0, $largeur, 16, imagesx($image_texture2), 16);
    }
    imagedestroy($image_texture2);

    foreach($pos->tranches as $src_tranche=>$pos_tranches) {
        $src_tranche='http://www.ducksmanager.net/'.str_replace('_','=',$src_tranche);
        $image_tranche= use_or_fetch($src_tranche);
        foreach($pos_tranches as $i=>$pos_tranche) {
            if ($debug) echo '<br />'.$i.' : '.($pos_courante[0]-$pos_sup_gauche[0]).','.($pos_courante[1]-$pos_sup_gauche[1]);
            $pos_courante=explode(',',$pos_tranche);
            imagecopyresampled($im, $image_tranche, $pos_courante[0]-$pos_sup_gauche[0], $pos_courante[1]-$pos_sup_gauche[1], 0, 0, $pos_courante[2], $pos_courante[3], imagesx($image_tranche), imagesy($image_tranche));
        }
        imagedestroy($image_tranche);
    }
    //echo $nom_image;
    if (!$debug) {
        header('Content-type: image/png');
        imagepng($im);
    }
}

function use_or_fetch($src_image) {
    global $debug;
    $src_image=preg_replace('#\?.*#is', '',str_replace('http://localhost/DucksManager/','',$src_image));
    if ($debug) echo '<br />'.$src_image;
    //if (!file_exists($src_image)) {
        if ($debug) echo ' - Recuperation depuis ducksmanager.net';
        $src_image=str_replace(' ','%20',$src_image);
        if (strpos($src_image, '.png') == strlen($src_image)-4) {
            $im=imagecreatefrompng($src_image);
            if ($im===false && $debug) {
                echo 'Erreur recuperation PNG '.$src_image.'<br />';
                return null;
            }
            imagepng($im,$src_image);
        }
        elseif (strpos($src_image, '.jpg') == strlen($src_image)-4) {
            $im=imagecreatefromjpeg($src_image);
            if ($im===false && $debug) {
                echo 'Erreur recuperation JPG '.$src_image.'<br />';
                exit(0);
            }
            imagejpeg($im,$src_image);
        }
        else {
            echo 'Erreur extension inconnue recuperation '.$src_image.'<br />';
            exit(0);
        }
        return $im;
    //}
    if (strpos($src_image, '.png') == strlen($src_image)-4) {
        $im=imagecreatefrompng($src_image);
        if ($im===false && $debug) {
            echo 'Erreur recuperation PNG existante'.$src_image.'<br />';
            exit(0);
        }
        return $im;
    }
    elseif (strpos($src_image, '.jpg') == strlen($src_image)-4) {
        $im=imagecreatefromjpeg($src_image);
        if ($im===false && $debug) {
            echo 'Erreur recuperation JPG existante'.$src_image.'<br />';
            exit(0);
        }
        return $im;
    }

}