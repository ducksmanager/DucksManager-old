<?php
class fr_JP extends Edge {
    var $pays='fr';
    var $magazine='JP';
    var $intervalles_validite=array(0,1,2);

    static $largeur_defaut=15;
    static $hauteur_defaut=278;
    
    function fr_JP ($numero) {
        $this->numero=$numero;

        $this->hauteur=278*Edge::$grossissement;
        $this->largeur=15*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {

        if ($this->numero == 0) {
            $this->placer_image('JP.0.tranche.png');
            return $this->image;
        }
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');
        $texte='La Jeunesse de Picsou';
        $auteur='D O N  R O S A';
        $police_auteur='Compacta Bold Italic.ttf';
        list($rouge_auteur,$vert_auteur,$bleu_auteur)=$this->getColorsFromDB(array(255,255,255),'Texte auteur');
        $couleur_texte_auteur=imagecolorallocate($this->image, $rouge_auteur,$vert_auteur,$bleu_auteur);
        
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
        $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
        $couleur_texte=imagecolorallocate($this->image, $rouge_texte, $vert_texte, $bleu_texte);


        imagefill($this->image,0,0,$fond);
        $this->placer_image('Logo PM JP.png','haut',array(0,$this->largeur*0.3));

        $texte=new Texte($texte,$this->largeur*7/10,$this->hauteur*0.7,
                        6.5*Edge::$grossissement,90,$couleur_texte,'Incised.ttf');
        $texte->dessiner($this->image);

        $texte=new Texte($auteur,$this->largeur*7/10,$this->hauteur-$this->largeur*1.8,
                    5.5*Edge::$grossissement,90,$couleur_texte_auteur,$police_auteur);
        $texte->dessiner($this->image);

        $this->placer_image('Tete PM.png','bas',array($this->largeur*0.15,0),0.7,0.7);

        return $this->image;
    }
}
?>
