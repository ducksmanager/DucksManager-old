<?php
class fr_MHC extends Edge {
    var $pays='fr';
    var $magazine='MHC';
    var $intervalles_validite=array(1);
    static $largeur_defaut=14;
    static $hauteur_defaut=204;
    
    function fr_MHC($numero) {
        $this->numero=$numero;

        $this->largeur=14*Edge::$grossissement;
        $this->hauteur=204*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        $noir=imagecolorallocate($this->image, 0,0,0);
        $fond=imagecolorallocate($this->image, 35, 100, 135);
        imagefill($this->image, 0, 0, $fond);
        $this->placer_image('MHC.1.tranche.png');
        $this->placer_image('signature Disney.png','haut',array($this->largeur*0.1,0.67*$this->largeur),0.8,0.8);
        $texte_numero=new Texte('HACHETTE',$this->largeur*0.15,$this->hauteur-$this->largeur*1.6,
                                    1.4*Edge::$grossissement,0,$noir,'SorbonneBQRegular.ttf');
        $texte_numero->dessiner($this->image);

        return $this->image;
    }
}
?>
