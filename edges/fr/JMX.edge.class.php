<?php
class JMX extends Edge {
    var $pays='fr';
    var $magazine='JMX';
    var $intervalles_validite=array(1);
    static $largeur_defaut=13;
    static $hauteur_defaut=275;

    function JMX ($numero) {
        $this->numero=$numero;
        $this->largeur=13*Edge::$grossissement;
        $this->hauteur=275*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {
        $fond=imagecolorallocate($this->image, 191, 152, 85);
        imagefill($this->image, 0, 0, $fond);
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        $this->placer_image('Logo JMX.png', 'haut', array(0.15*$this->largeur,0.75*$this->largeur),0.7,0.7);
        $texte_numero=new Texte('Les tr&#233;sors du Journal de Mickey',$this->largeur*0.7,$this->hauteur-$this->largeur*0.8,
                                6.5*Edge::$grossissement,90,$noir,'Block Berthold Regular.ttf');
        $texte_numero->dessiner($this->image);
        return $this->image;
    }

}