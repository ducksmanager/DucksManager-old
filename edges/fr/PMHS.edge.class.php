<?php
class PMHS extends Edge {
    var $pays='fr';
    var $magazine='PMHS';
    var $intervalles_validite=array('B  1','B  2','B  3','C  1','C  2','C  3','C  4','C  5','C  6','C  7','C  8','C  9','C 10','C 11');

    static $largeur_defaut=15;
    static $hauteur_defaut=278;

    var $serie;
    var $numero_serie;

    function PMHS ($numero) {
        $this->numero=$numero;
        $this->serie=$numero[0];
        $this->numero_serie=substr($this->numero, strrpos($this->numero, ' ')+1, strlen($this->numero));
        

        $this->hauteur=278*Edge::$grossissement;
        $this->largeur=15*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {

        if ($this->serie == 'B' && $this->numero_serie == 1) {
            $this->placer_image('PMHS.B1.tranche.png');
            return $this->image;
        }
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');
        switch($this->serie) {
            case 'B': case 'C':
                if ($this->serie == 'B') {
                    $texte='La Jeunesse de Picsou';
                    $auteur='D O N  R O S A';
                    $police_auteur='Compacta Bold Italic.ttf';
                    list($rouge_auteur,$vert_auteur,$bleu_auteur)=$this->getColorsFromDB(array(255,255,255),'Texte auteur');
                    $couleur_texte_auteur=imagecolorallocate($this->image, $rouge_auteur,$vert_auteur,$bleu_auteur);
                }
                else {
                    $texte='Les T';
                    if ($this->serie == 'C' && $this->numero_serie==1)
                        $auteur='D O N  R O S A';
                    else
                        $auteur='D   I   S   N   E   Y';
                    $police_auteur='Compacta Bold.ttf';
                    $couleur_texte_auteur=imagecolorallocate($this->image, 230,216,117);
                }
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
                $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                $couleur_texte=imagecolorallocate($this->image, $rouge_texte, $vert_texte, $bleu_texte);
                

                imagefill($this->image,0,0,$fond);
                $this->placer_image('Logo PM PMHS.png','haut',array(0,$this->largeur*0.3));

                $texte=new Texte($texte,$this->largeur*7/10,$this->hauteur*0.7,
                                6.5*Edge::$grossissement,90,$couleur_texte,'Incised.ttf');
                $texte->dessiner($this->image);

                if ($this->serie == 'C') {
                    $texte=new Texte('résors de Picsou',$this->largeur*7/10,$this->hauteur*0.575,
                                6.5*Edge::$grossissement,90,$couleur_texte,'Incised.ttf');
                $texte->dessiner($this->image);
                }

                $texte=new Texte($auteur,$this->largeur*7/10,$this->hauteur-$this->largeur*1.8,
                            5.5*Edge::$grossissement,90,$couleur_texte_auteur,$police_auteur);
                $texte->dessiner($this->image);
                
                $this->placer_image('Tete PM.png','bas',array($this->largeur*0.15,0),0.7,0.7);
            break;
        }
        return $this->image;
    }
}
?>
