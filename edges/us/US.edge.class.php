<?php
class US extends Edge {
    var $pays='us';
    var $magazine='US';
    var $intervalles_validite=array('339','342','359','365');
    var $en_cours=array();
    static $largeur_defaut=4;
    static $hauteur_defaut=258;


    function US($numero) {
        $this->numero=$numero;
        $this->largeur=4*Edge::$grossissement;
        $this->hauteur=258*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        list($rouge,$vert,$bleu)=$this->getColorsFromDB();
        $fond=imagecolorallocate($this->image, $rouge,$vert,$bleu);
        imagefill($this->image,0,0,$fond);
        $texte=new Texte('WALT DISNEY\'S UNCLE SCROOGE '.$this->numero,$this->largeur*8/10,$this->hauteur-$this->largeur*2.5,
                            2*Edge::$grossissement,90,$noir,'Gill Sans Bold.ttf');
        $texte->dessiner($this->image);

        
        $texte=new Texte('GEMSTONE',$this->largeur*8/10,$this->largeur*6,
                            2*Edge::$grossissement,90,$noir,'Gill Sans Bold.ttf');
        $texte->dessiner($this->image);
        
        return $this->image;
    }
}
?>
