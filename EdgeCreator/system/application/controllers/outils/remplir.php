<?php
include_once('outil.php');

class Remplir extends Outil {
    static $arguments=array('IMAGE','x','y','COULEUR0');
    static $fonction='imagefill';
    var $x;
    var $y;


    function index() {
        Outil::$liste_images=array('image2'=>imagecreatetruecolor(200, 200));
        $r=new Remplir();
        $r->x=1;
        $r->y=2;
        $r->image='image2';
        Outil::$liste_couleurs=array('couleur1'=>imagecolorallocate(Outil::$liste_images[$r->image],25,26,87));
        $r->action();
        echo $r->toCode();
    }
}

?>
