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
    static $grossissement=3;
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

    function getColorsFromDB() {
        include_once('Database.class.php');
        $d=new Database();
        $requete_couleurs='SELECT CouleurR, CouleurG, CouleurB FROM bibliotheque_options WHERE Pays LIKE \''.$this->pays.'\' AND Magazine LIKE \''.$this->magazine.'\' AND Numéro LIKE \''.$this->numero.'\'';
        $resultat=$d->requete_select($requete_couleurs);
        return array($resultat[0]['CouleurR'], $resultat[0]['CouleurG'], $resultat[0]['CouleurB']);
    }

    function getDataFromDB() {
        include_once('Database.class.php');
        $d=new Database();
        $requete_couleurs='SELECT Autre FROM bibliotheque_options WHERE Pays LIKE \''.$this->pays.'\' AND Magazine LIKE \''.$this->magazine.'\' AND Numéro LIKE \''.$this->numero.'\'';
        $resultat=$d->requete_select($requete_couleurs);
        return $resultat[0]['Autre'];
    }
}
if (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numero'])) {
    if (!isset($_GET['debug']))
        header('Content-type: image/png');
    $e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero']);
    $o=$e->o;
    $o->dessiner_tranche();
}
/*
 *
CREATE TABLE `bibliotheque_options` (  `Pays` VARCHAR(3) NULL,  `Magazine` VARCHAR(6) NULL,  `Numéro` VARCHAR(8) NULL,  `CouleurR` TINYINT(8) UNSIGNED NULL DEFAULT '0',  `CouleurG` TINYINT(8) UNSIGNED NULL DEFAULT '0',  `CouleurB` TINYINT(8) UNSIGNED NULL DEFAULT '0',  `Autre` TEXT NULL ) COLLATE='latin1_german2_ci' ENGINE=MyISAM ROW_FORMAT=DEFAULT;
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '142', 142, 85, 114);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '143', 243, 67, 77);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '144', 44, 156, 167);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '145', 238, 74, 3);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '146', 233, 65, 127);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '147', 21, 143, 190);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '148', 249, 182, 39);
INSERT INTO `bibliotheque_options` (`Pays`, `Magazine`, `Numéro`, `CouleurR`, `CouleurG`, `CouleurB`) VALUES ('fr', 'SPG', '149', 231, 56, 61);
ALTER TABLE `users`  ADD COLUMN `Bibliotheque_Texture1` VARCHAR(20) NOT NULL DEFAULT 'Bois' AFTER `RecommandationsListeMags`;
ALTER TABLE `users`  ADD COLUMN `Bibliotheque_Sous_Texture1` VARCHAR(50) NOT NULL DEFAULT 'HONDURAS MAHOGANY' AFTER `Bibliotheque_Texture1`;
ALTER TABLE `users`  ADD COLUMN `Bibliotheque_Texture2` VARCHAR(20) NOT NULL DEFAULT 'Bois' AFTER `Bibliotheque_Sous_Texture1`;
ALTER TABLE `users`  ADD COLUMN `Bibliotheque_Sous_Texture2` VARCHAR(50) NOT NULL DEFAULT 'KNOTTY PINE' AFTER `Bibliotheque_Texture2`;
 */
if (isset($_POST['get_texture'])) {
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
                echo 'selected="selected"';?>
            ><?=$f?></option>
            <?php
        }
    }
}

if (isset($_POST['get_sous_texture'])) {
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

function getImgHTMLOf($pays,$magazine,$numero) {
    $e=new Edge($pays, $magazine, $numero);
    return array($e->getImgHTML(),$e->est_visible);
}