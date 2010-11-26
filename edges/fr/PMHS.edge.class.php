<?php
class fr_PMHS extends Edge {
    var $pays='fr';
    var $magazine='PMHS';
    var $intervalles_validite=array('D1');
    static $largeur_defaut=6;
    static $hauteur_defaut=274;
    var $lettre;
    
    function fr_PMHS ($numero) {
        $this->numero=$numero;
        $numero_str=str_split($this->numero);
        $this->lettre=$numero_str[0];
        switch($this->lettre) {
            case 'D':
                $this->largeur=6*Edge::$grossissement;
                $this->hauteur=274*Edge::$grossissement;
            break;
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        switch($this->lettre) {
            case 'D':
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
                $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                imagefill($this->image, 0, 0, $fond);
                $this->placer_image('PMHS.'.$this->numero.'.Titre.png','haut',array(0,$this->hauteur*0.29));
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255),'Couleur 2');
                $couleur2=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                imageline($this->image, 0, $this->largeur*4.5, $this->largeur, $this->largeur*4.2,$couleur2);
                imagefill($this->image,$this->largeur/2,$this->largeur,$couleur2);
                imageline($this->image, 0, $this->hauteur-$this->largeur*0.3, $this->largeur, $this->hauteur-$this->largeur*0.4,$couleur2);
                imagefill($this->image,$this->largeur/2,$this->hauteur-$this->largeur*0.1,$couleur2);
                
                $this->placer_image('Tete PM.png', 'bas', array(0,$this->largeur/2));
            break;
        }
            
        return $this->image;
    }

}