<?php
class fr_DM extends Edge {
    var $pays='fr';
    var $magazine='DM';
    var $intervalles_validite=array('85-01','85-02','85-03','85-04','85-05','85-06','85-07','85-08','85-09','85-10','85-11','85-12','85-13','85-14','85-15','85-16','85-17','85-18','85-19','85-20','85-21','85-22','85-23','85-24','85-25','85-26','85-27','85-28','85-29','85-30','85-31','85-32','85-33','85-34','85-35','85-36','85-37','85-38','85-39','85-40','85-41','85-42','85-43','85-44','85-45','85-46','85-47','85-48','85-49','85-50','85-51','85-52','85-53',
                                    '86-01','86-02','86-03','86-04','86-05','86-06','86-07','86-08','86-09','86-10','86-11','86-12','86-13','86-14','86-15','86-16','86-17','86-18','86-19','86-20','86-21','86-22','86-23','86-24','86-25','86-26','86-27','86-28','86-29','86-30','86-31','86-32','86-33','86-34','86-35','86-36','86-37','86-38','86-39','86-40','86-41','86-42','86-43','86-44','86-45','86-46','86-47','86-48','86-49','86-50','86-51','86-52');
    static $largeur_defaut=3;
    static $hauteur_defaut=285;

    function fr_DM ($numero) {
        $this->numero=$numero;
        $this->hauteur=285;
        $this->largeur=3;
        $this->hauteur*=Edge::$grossissement;
        $this->largeur*=Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        $blanc = imagecolorallocate($this->image, 255, 255, 255);
        $noir = imagecolorallocate($this->image, 0, 0, 0);
        imagefill($this->image,0,0,$blanc);
        $this->agrafer();
        return $this->image;
    }

}