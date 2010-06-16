<?php
include_once('Texte.class.php');
include_once('IntervalleValidite.class.php');
class Edge {
	var $pays;
	var $magazine;
	var $numero;
	var $textes=array();
	var $largeur=20;
	var $hauteur=200;
    var $image;
    var $o;
	var $est_visible=true;
    var $intervalles_validite=array();
    static $grossissement=1.5;
    static $largeur_numeros_precedents=0;
    
	function Edge($pays=null,$magazine=null,$numero=null) {
            if (is_null($pays))
                return;
            $this->pays=$pays;$this->magazine=$magazine;$this->numero=$numero;
            if (file_exists('edges/'.$this->pays.'/'.$this->magazine.'.edge.class.php')) {
                require_once('edges/'.$this->pays.'/'.$this->magazine.'.edge.class.php');
                $this->o=new $this->magazine($this->numero);
                $intervalle_validite=new IntervalleValidite($this->o->intervalles_validite);
                if (!$intervalle_validite->estValide($this->numero))
                    $this->est_visible=false;
            }
            else {
                $this->o=clone $this;
                $this->est_visible=false;
            }
	}

    static function getEtagereHTML($br=true) {
        $code= '<div class="etagere" style="width:'.Etagere::$largeur.';'
                                          .'background-image: url(\'edges/textures/'.Etagere::$texture2.'/'.Etagere::$sous_texture2.'.jpg\')">&nbsp;</div>';
        if ($br===true)
            $code.= '<br />';
        return $code;
    }
        function getImgHTML() {
            $code='';
            if (Edge::$largeur_numeros_precedents + $this->o->largeur > Etagere::$largeur) {
                $code.=Edge::getEtagereHTML();
                Edge::$largeur_numeros_precedents=0;
            }
            if ($this->o->hauteur > Etagere::$hauteur_max_etage)
                Etagere::$hauteur_max_etage = $this->o->hauteur ;
            $code.= '<img class="tranche" '
                  .'name="Edge.class.php?pays='.$this->pays.'&amp;magazine='.$this->magazine.'&amp;numero='.$this->numero.'" '
                  .'width="'.$this->o->largeur.'" height="'.$this->o->hauteur.'" />';
            Edge::$largeur_numeros_precedents+=$this->o->largeur;
            return $code;
        }

        function dessiner_tranche() {
            $intervalle_validite=new IntervalleValidite($this->intervalles_validite);
            if ($intervalle_validite->estValide($this->numero))
                $this->image=$this->dessiner();
            else
                $this->image=$this->dessiner_defaut();
            foreach($this->textes as $texte) {
                imagettftext($this->image,$texte->taille,$texte->angle,$texte->pos_x,$texte->pos_y,$texte->couleur,$texte->police,$texte->texte);
            }
            imageantialias($this->image, true);
            imagepng($this->image);
        }

	function dessiner_defaut() {
            $this->image=imagecreatetruecolor($this->largeur,$this->hauteur);
            $blanc=imagecolorallocate($this->image,255,255,255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            imagefilledrectangle($this->image, 0, 0, $this->largeur-2, $this->hauteur-2, $blanc);
            imagerectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $noir);
            imagettftext($this->image,7*Edge::$grossissement,90,$this->largeur*7/10,$this->hauteur-$this->largeur*4/5,
			 $noir,'edges/Verdana.ttf','['.$this->pays.' / '.$this->magazine.' / '.$this->numero.']');
            imageantialias($this->image, true);
            return $this->image;
	}
}
if (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numero'])) {
    if (!isset($_GET['debug']))
        header('Content-type: image/png');
    $e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero']);
    $o=$e->o;
    $o->dessiner_tranche();
}

function getImgHTMLOf($pays,$magazine,$numero) {
    $e=new Edge($pays, $magazine, $numero);
    return array($e->getImgHTML(),$e->est_visible);
}