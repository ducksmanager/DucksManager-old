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
    var $en_cours=array();
    static $grossissement=10;
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
            $this->largeur*=Edge::$grossissement;
            $this->hauteur*=Edge::$grossissement;
        }
	}

    static function getEtagereHTML($br=true) {
        $code= '<div class="etagere" style="width:'.Etagere::$largeur.';'
                                          .'background-image: url(\'edges/textures/'.Etagere::$texture2.'/'.Etagere::$sous_texture2.'.jpg\')">&nbsp;</div>';
        if ($br===true)
            $code.= '<br />';
        return $code;
    }
    function getImgHTML($regen=false) {
        $code='';
        if (Edge::$largeur_numeros_precedents + $this->o->largeur > Etagere::$largeur) {
            $code.=Edge::getEtagereHTML();
            Edge::$largeur_numeros_precedents=0;
        }
        if ($this->o->hauteur > Etagere::$hauteur_max_etage)
            Etagere::$hauteur_max_etage = $this->o->hauteur ;
        $code.= '<img class="tranche" ';
        if ($regen) {
            $code.='name="Edge.class.php?pays='.$this->pays.'&amp;magazine='.$this->magazine.'&amp;numero='.$this->numero.'&amp;grossissement='.Edge::$grossissement.'" ';
        }
        else {
            $code.='name="edges/'.$this->pays.'/gen/'.$this->magazine.'.'.$this->numero.'.png" ';
        }
        $code.='width="'.$this->o->largeur.'" height="'.$this->o->hauteur.'" />';
        Edge::$largeur_numeros_precedents+=$this->o->largeur;
        return $code;
    }

    function dessiner_tranche($regen=false) {
        $intervalle_validite=new IntervalleValidite($this->intervalles_validite);
        if ($intervalle_validite->estValide($this->numero))
            $this->image=$this->dessiner();
        else
            $this->image=$this->dessiner_defaut();
        $this->dessiner_contour();
        foreach($this->textes as $texte) {
            imagettftext($this->image,$texte->taille,$texte->angle,$texte->pos_x,$texte->pos_y,$texte->couleur,$texte->police,$texte->texte);
        }
        imageantialias($this->image, true);
        if (!is_dir('edges/'.$this->pays)) {
            mkdir('edges/'.$this->pays);
            mkdir('edges/'.$this->pays.'/gen');
        }
        imagepng($this->image,'edges/'.$this->pays.'/gen/'.$this->magazine.'.'.$this->numero.'.png');
        imagepng($this->image);
    }

	function dessiner_defaut() {
        $this->image=imagecreatetruecolor($this->largeur,$this->hauteur);
        $blanc=imagecolorallocate($this->image,255,255,255);
        $noir = imagecolorallocate($this->image, 0, 0, 0);
        imagefilledrectangle($this->image, 0, 0, $this->largeur-2, $this->hauteur-2, $blanc);
        imagettftext($this->image,$this->largeur/3,90,$this->largeur*7/10,$this->hauteur-$this->largeur*4/5,
         $noir,'edges/Verdana.ttf','['.$this->pays.' / '.$this->magazine.' / '.$this->numero.']');
        imageantialias($this->image, true);
        return $this->image;
	}

    function getColorsFromDB($default_color=array(255,255,255),$parametre_autre=null) {
        include_once('Database.class.php');
        $d=new Database();
        $requete_couleurs='SELECT CouleurR, CouleurG, CouleurB FROM bibliotheque_options WHERE Pays LIKE \''.$this->pays.'\' AND Magazine LIKE \''.$this->magazine.'\' AND Numéro LIKE \''.$this->numero.'\'';
        if (!is_null($parametre_autre))
            $requete_couleurs.=' AND Autre LIKE \''.$parametre_autre.'\'';
        $resultat=$d->requete_select($requete_couleurs);
        if (count($resultat)==0)
            return $default_color;
        return array($resultat[0]['CouleurR'], $resultat[0]['CouleurG'], $resultat[0]['CouleurB']);
    }

    function getDataFromDB($default_text='') {
        include_once('Database.class.php');
        $d=new Database();
        $requete_couleurs='SELECT Autre FROM bibliotheque_options WHERE Pays LIKE \''.$this->pays.'\' AND Magazine LIKE \''.$this->magazine.'\' AND Numéro LIKE \''.$this->numero.'\'';
        $resultat=$d->requete_select($requete_couleurs);
        if (count($resultat)==0)
            return $default_text;
        return $resultat[0]['Autre'];
    }

    function agrafer() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        for ($i=-.25*Edge::$grossissement;$i<.25*Edge::$grossissement;$i++) {
            imageline($this->image, $this->largeur/2 - $i, $this->hauteur/5, $this->largeur/2 - $i, $this->hauteur/4, $noir);
            imageline($this->image, $this->largeur/2 - $i, $this->hauteur*4/5, $this->largeur/2 - $i, $this->hauteur*4/5 - ($this->hauteur/4 - $this->hauteur/5), $noir);
        }
    }

    function dessiner_contour() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        for ($i=0;$i<.15*Edge::$grossissement;$i++)
            imagerectangle($this->image, $i, $i, $this->largeur-1-$i, $this->hauteur-1-$i, $noir);
    }

    static function getPourcentageVisible($get_html=false, $regen=false) {
        include_once('Database.class.php');
        @session_start();
        $d=new Database();
        $id_user=$d->user_to_id($_SESSION['user']);
        $l=$d->toList($id_user);
        $texte_final='';
        $total_numeros=0;
        $total_numeros_visibles=0;
        foreach($l->collection as $pays=>$magazines) {
            foreach($magazines as $magazine=>$numeros) {
				sort($numeros);
                $total_numeros+=count($numeros);
				foreach($numeros as $numero) {
                    if ($get_html) {
                        list($texte,$est_visible)=getEstVisible($pays, $magazine, $numero[0],true, $regen);
                        $texte_final.=$texte;
                    }
                    else
                        $est_visible=getEstVisible($pays, $magazine, $numero[0]);
                    if ($est_visible===true)
                        $total_numeros_visibles++;
                }
            }
        }
        $pourcentage_visible=intval(100*$total_numeros_visibles/$total_numeros);
        if ($get_html)
            return array($texte_final, $pourcentage_visible);
        else
            return $pourcentage_visible;
    }

}
if (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numero'])) {
    if (isset($_GET['grossissement']))
        Edge::$grossissement=$_GET['grossissement'];
    if (!isset($_GET['debug']))
        header('Content-type: image/png');
    $e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero']);
    $o=$e->o;
    $regen=isset($_GET['regen']);
    $o->dessiner_tranche($regen);
}
/*
 * Table bibliotheque_options
*/
elseif (isset($_POST['get_texture'])) {
    include_once('Database.class.php');
    $d=new Database();
    if (!$d) {
        echo PROBLEME_BD;
        exit(-1);
    }
    $id_user=$d->user_to_id($_SESSION['user']);
    $requete_texture='SELECT Bibliotheque_Texture'.$_POST['n'].' FROM users WHERE ID LIKE \''.$id_user.'\'';
    $resultat_texture=$d->requete_select($requete_texture);
	$rep = "edges/textures";
    $dir = opendir($rep);
    while ($f = readdir($dir)) {
        if( $f!=='.' && $f!=='..') {
            ?>
            <option 
            <?php
            if ($f==$resultat_texture[0]['Bibliotheque_Texture'.$_POST['n']])
                echo 'selected="selected" ';?>
            value="<?=$f?>"
            ><?=constant('TEXTURE__'.strtoupper($f))?></option>
            <?php
        }
    }
}

elseif (isset($_POST['get_sous_texture'])) {
    include_once('Database.class.php');
    $d=new Database();
    if (!$d) {
        echo PROBLEME_BD;
        exit(-1);
    }
    $id_user=$d->user_to_id($_SESSION['user']);
    $requete_texture='SELECT Bibliotheque_Sous_Texture'.$_POST['n'].' FROM users WHERE ID LIKE \''.$id_user.'\'';
    $resultat_texture=$d->requete_select($requete_texture);

	$rep = 'edges/textures/'.$_POST['texture'].'/miniatures';
    $dir = opendir($rep);
    while ($f = readdir($dir)) {
        if( $f!=='.' && $f!=='..') {
            $nom_sous_texture=substr($f,0, strrpos($f, '.'));
            ?>
            <option <?php
            if ($nom_sous_texture==$resultat_texture[0]['Bibliotheque_Sous_Texture'.$_POST['n']])
                echo 'selected="selected" ';?>
            style="background:url('edges/textures/<?=$_POST['texture']?>/miniatures/<?=$f?>') no-repeat scroll center right transparent">
                <?=$nom_sous_texture?>
            </option><?php
        }
    }
}

function getEstVisible($pays,$magazine,$numero, $get_html=false, $regen=false) {
    $e=new Edge($pays, $magazine, $numero);
    if ($get_html)
        return array($e->getImgHTML($regen),$e->est_visible);
    else
        return $e->est_visible;
}