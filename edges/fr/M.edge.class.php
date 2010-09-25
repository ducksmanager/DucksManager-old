<?php
class M extends Edge {
    var $pays='fr';
    var $magazine='M';
    var $intervalles_validite=array(6,27,29,31,33,35,70,71);
    static $largeur_defaut=9;
    static $hauteur_defaut=207;

    function M ($numero) {
        $this->numero=$numero;
        $this->largeur=9*Edge::$grossissement;
        $this->hauteur=207*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
        list($rouge2,$vert2,$bleu2)=$this->getColorsFromDB(array(255,255,255),'Couleur 2');
        $fond=imagecolorallocate($this->image, $rouge,$vert,$bleu);
        $couleur2=imagecolorallocate($this->image, $rouge2,$vert2,$bleu2);
        imagefill($this->image, 0, 0, $fond);
        if (file_exists($this->getChemin().'/M.'.$this->numero.'.dessin.png')) {
            $this->placer_image('M.'.$this->numero.'.dessin.png','bas');
        }
        $this->placer_image($this->numero<27 ? 'M.Logo1.png' : 'M.Logo2.png','bas',array(0,$this->largeur*($this->numero<27 ? 1.7 : 2.2)));
        
        list($coeur,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/M.coeur.png');
        imagefill($coeur,$width/2,$height/2,$couleur2);
        $this->placer_image($coeur,'haut',array(0,$this->largeur/3));
        if ($this->numero == 71) {// Différente couleur pour le coeur du bas
            list($rouge3,$vert3,$bleu3)=$this->getColorsFromDB(array(255,255,255),'Couleur 3');
            $couleur3=imagecolorallocate($this->image, $rouge3,$vert3,$bleu3);
            imagefill($coeur,$width/2,$height/2,$couleur3);
        }
        $this->placer_image($coeur,'bas');
        $chiffres=imagecreatetruecolor($this->largeur, $this->largeur);
        $fond_chiffres=imagecolorallocate($chiffres, $rouge, $vert, $bleu);
        imagefill($chiffres, 0, 0, $fond_chiffres);
        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
        $largeurs=array();
        $chiffres=array();
        foreach(str_split($this->numero) as $i=>$chiffre) {
            list($chiffre,$c_width,$c_height)=imagecreatefrompng_getimagesize($this->getChemin().'/M.Chiffre.'.$chiffre.'.png');
            $noir=imagecolorallocate($chiffre,0,0,0);
            remplacerCouleur($chiffre,0,0,0,$rouge_texte,$vert_texte,$bleu_texte);
            //$noir= imagecolorresolve($chiffre, 0, 0, 0);
            imagecolorset($chiffre, $noir, 128, 128, 128);
            $largeur=$this->largeur/2.8;
            $hauteur=$largeur*($c_height/$c_width);
            $chiffres[]=$chiffre;
            imagecopyresampled ($this->image, $chiffre, $this->largeur/2-(count(str_split($this->numero))*$largeur)/2+$i*$largeur,$this->largeur*0.75, 0, 0, $largeur, $hauteur, $c_width, $c_height);
            $largeurs[]=$largeur;
        }
        return $this->image;
    }

}