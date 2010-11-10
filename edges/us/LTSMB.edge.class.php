<?php
class LTSMB extends Edge {
    var $pays='us';
    var $magazine='LTSMB';
    var $intervalles_validite=array('1','2');
    var $en_cours=array();
    static $largeur_defaut=20;
    static $hauteur_defaut=258;


    function LTSMB($numero) {
        $this->numero=$numero;
        $this->largeur=17.2*Edge::$grossissement;
        $this->hauteur=277*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        $fond=imagecolorallocate($this->image,179,143,93);
        imagefill($this->image,0,0,$fond);
        $texte=new Texte('THE LIFE AND TIMES OF SCROOGE MCDUCK',$this->largeur*3.5/10,$this->largeur*1.7,
                            6*Edge::$grossissement,-90,$noir,'Benguiat Book BT.ttf');
        $texte->dessiner($this->image);
        $texte=new Texte('DON ROSA',$this->largeur*3.5/10,$this->hauteur-$this->largeur*3,
                            6*Edge::$grossissement,-90,$noir,'Benguiat Book.ttf');
        $texte->dessiner($this->image);
        $texte=new Texte($this->numero,$this->largeur*3.5/10,$this->hauteur-$this->largeur*3.6,
                            6*Edge::$grossissement,0,$noir,'Benguiat Book.ttf');
        $texte->dessiner($this->image);
        $this->placer_image('LTSMB.Boom.png');
        
        return $this->image;
    }
}
?>
