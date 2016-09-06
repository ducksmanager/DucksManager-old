<?php
include_once('IntervalleValidite.class.php');
include_once('DucksManager_Core.class.php');
include_once('getDimensions.php');
include_once('Etagere.class.php');

class Edge {
	var $pays;
	var $magazine;
	var $numero;
	var $numero_reference;
	var $textes= [];
	var $largeur=20;
	var $hauteur=200;
	var $image;
	var $image_existe;
	var $o;
	var $est_visible=true;
	var $magazine_est_inexistant=false;
	var $intervalles_validite= [];
	var $en_cours= [];
	var $html='';
	static $grossissement=10;
	static $grossissement_affichage=1.5;
	static $grossissement_defaut=1.5;
	static $largeur_numeros_precedents=0;
	static $d;
	static $sans_etageres = false;
	
	function __construct($pays=null,$magazine=null,$numero=null,$numero_reference=null,$image_seulement=false) {
		if (is_null($pays))
			return;
		$this->magazine_est_inexistant=false;
		$this->pays=$pays;
		$this->magazine=$magazine;
		$this->numero= $numero;
		$this->numero_reference= $numero_reference;
		
		$dossier_image = 'edges/'.$this->pays.'/gen/';
		$url_image=$dossier_image.$this->magazine.'.'.$this->numero_reference.'.png';
		if ($image_seulement) {
			if (!file_exists($url_image)) {
				mkdir($dossier_image, 0777, true);
				imagepng($this->dessiner_defaut(),$url_image);
			}
			$this->image=@imagecreatefrompng($url_image);
		}
		else {
			$this->est_visible=getEstVisible($this->pays,$this->magazine,$this->numero_reference);

			$this->image_existe=file_exists($url_image);
			if ($this->image_existe) {
				if (! (list($this->largeur,$this->hauteur,$type,$attr)=@getimagesize($url_image))) {
					mail('admin@ducksmanager.net', 'Image de bibliothèque corrompue',$url_image);
					return;
				}
				$this->largeur=intval( $this->largeur / (Edge::$grossissement_affichage/Edge::$grossissement_defaut));
				$this->hauteur=intval( $this->hauteur / (Edge::$grossissement_affichage/Edge::$grossissement_defaut));

			}
			else {
				$dimensions=getDimensionsParDefautMagazine($this->pays,$this->magazine, [$this->numero_reference]);
				if (!is_null($dimensions[$this->numero_reference]) && $dimensions[$this->numero_reference]!='null')
					list($this->largeur,$this->hauteur)=explode('x',$dimensions[$this->numero_reference]);

				if (!$this->image_existe) {
					@imagepng($this->dessiner_defaut(),$url_image);
				}

				$this->est_visible=false;
				$this->largeur*=Edge::$grossissement_affichage;
				$this->hauteur*=Edge::$grossissement_affichage;

				$this->magazine_est_inexistant=true;
			}
			$this->html=$this->getImgHTML();
		}
	}

	function getLargeurHauteurDefaut() {
		return [$this->largeur,$this->hauteur];
	}
	
	static function getEtagereHTML($br=true) {
		$code= '<div class="etagere" style="width:'.Etagere::$largeur.';'
										  .'background-image: url(\'edges/textures/'.Etagere::$texture2.'/'.Etagere::$sous_texture2.'.jpg\')">&nbsp;</div>';
		if ($br===true)
			$code.= '<br />';
		return $code;
	}

	static function get_numero_clean($numero) {
		return str_replace('+','',str_replace(' ','',$numero));
	}
	
	
	static function get_numeros_clean($pays,$magazine,$numeros) {
		$numeros_clean_et_references= [];
		foreach($numeros as $i=>$numero) {
			$numero=$numero[0];
			$numero_clean=self::get_numero_clean($numero);
			$numeros[$i]="'".$numero_clean."'";
			$numeros_clean_et_references[$numero]= ['clean'=>$numero_clean,'reference'=>$numero_clean];
		}
		
		$numeros_subarrays=array_chunk($numeros, 250);
		
		foreach($numeros_subarrays as $numeros_subarray) {
			$requete_recherche_numero_reference=
				'SELECT Numero,NumeroReference '
			   .'FROM tranches_doublons '
			   .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero IN ('.implode(',', $numeros_subarray).') ';
			$resultat_numero_reference=DM_Core::$d->requete_select_distante($requete_recherche_numero_reference);
			foreach($resultat_numero_reference as $numero_reference) {
				$numeros_clean_et_references[$numero_reference['Numero']]['reference']
					=$numero_reference['NumeroReference'];
			}
		}

		$vrai_magazine=Inducks::get_vrai_magazine($pays,$magazine);
		if ($vrai_magazine != $magazine) {
			foreach($numeros_clean_et_references as $numero) {
				$numero_clean = is_array($numero) ? $numero['clean'] : $numero;
				list($vrai_magazine,$vrai_numero) = Inducks::get_vrais_magazine_et_numero($pays,$magazine,$numero_clean);
				$numeros_clean_et_references[$numero_clean]['reference']=$vrai_numero;
			}
		}

		return [$vrai_magazine, $numeros_clean_et_references];
	}
	
	function getImgHTML($regen=false) {
		$code='';
		if (!Edge::$sans_etageres) {
			if (Edge::$largeur_numeros_precedents + $this->largeur > Etagere::$largeur) {
				$code .= Edge::getEtagereHTML();
				Edge::$largeur_numeros_precedents = 0;
			}
			if ($this->hauteur > Etagere::$hauteur_max_etage) {
				Etagere::$hauteur_max_etage = $this->hauteur;
			}
		}
		$code.= '<img class="tranche" ';
		
		if ($this->image_existe && !$regen) {
			$code.='name="'.$this->pays.'/'.$this->magazine.'.'.$this->numero_reference.'" ';
		}
		else {
			$code.='name="Edge.class.php?pays='.$this->pays.'&amp;magazine='.$this->magazine.'&amp;numero='.$this->numero_reference.'&amp;grossissement='.Edge::$grossissement.'" ';
		}
		$code.='width="'.$this->largeur.'" height="'.$this->hauteur.'" id="'.$this->pays.'/'.$this->magazine.'.'.$this->numero.'"/>';
		
		Edge::$largeur_numeros_precedents+=$this->largeur;
		return $code;
	}

	function dessiner_defaut() {
		$this->image=imagecreatetruecolor($this->largeur,$this->hauteur);
		$blanc=imagecolorallocate($this->image,255,255,255);
		$noir = imagecolorallocate($this->image, 0, 0, 0);
		imagefilledrectangle($this->image, 0, 0, $this->largeur-2, $this->hauteur-2, $blanc);
		imagettftext($this->image,$this->largeur/3.5,90,$this->largeur*7/10,$this->hauteur-$this->largeur*4/5,
		 			 $noir,'edges/Verdana.ttf','['.$this->pays.' / '.$this->magazine.' / '.$this->numero.']');
		$this->dessiner_contour();
		$gris_250=imagecolorallocate($this->image, 250,250,250);
		imageantialias($this->image, true);
		imagefilledrectangle($this->image, $this->largeur/4,$this->largeur/4, $this->largeur*3/4,$this->largeur*3/4,$gris_250);
		return $this->image;
	}

    function dessiner_contour() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        for ($i=0;$i<.15*Edge::$grossissement;$i++)
            imagerectangle($this->image, $i, $i, $this->largeur-1-$i, $this->hauteur-1-$i, $noir);
    }

	function placer_image($sous_image, $position='haut', $decalage= [0,0], $compression_largeur=1, $compression_hauteur=1) {
		if (is_string($sous_image)) {
			$extension_image=strtolower(substr($sous_image, strrpos($sous_image, '.')+1,strlen($sous_image)-strrpos($sous_image, '.')-1));
			$fonction_creation_image='imagecreatefrom'.$extension_image.'_getimagesize';
			$chemin_reel=(strpos($sous_image, 'images_myfonts')!==false) ? $sous_image : $this->getChemin().'/'.$sous_image;
			list($sous_image,$width,$height)=call_user_func($fonction_creation_image,$chemin_reel);
		}
		else {
			$width=imagesx($sous_image);
			$height=imagesy($sous_image);
		}
		$hauteur_sous_image=$this->largeur*($height/$width);
		if ($position=='bas') {
			$decalage[1]=$this->hauteur-$hauteur_sous_image-$decalage[1];
		}
		imagecopyresampled ($this->image, $sous_image, $decalage[0], $decalage[1], 0, 0, $this->largeur*$compression_largeur, $hauteur_sous_image*$compression_hauteur, $width, $height);
		return $sous_image;
	}

	static function getPourcentageVisible($id_user, $get_html=false) {
		include_once('Database.class.php');
		global $numeros_inducks;
		@session_start();

        $l=DM_Core::$d->toList($id_user);
        $texte_final='';
        $total_numeros=0;
        $total_numeros_visibles=0;
        DM_Core::$d->maintenance_ordre_magazines($id_user);
        $requete_ordre_magazines='SELECT Pays,Magazine,Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' ORDER BY Ordre';
        $resultat_ordre_magazines=DM_Core::$d->requete_select_distante($requete_ordre_magazines);
        $publication_codes= [];
        foreach($resultat_ordre_magazines as $ordre) {
            $pays=$ordre['Pays'];
            $magazine=$ordre['Magazine'];
            $publication_codes[]=$pays.'/'.$magazine;
        }
        $numeros_inducks = Inducks::get_numeros_liste_publications($publication_codes);
        getDimensionsParDefaut($publication_codes);
        foreach($resultat_ordre_magazines as $ordre) {
            $pays=$ordre['Pays'];
            $magazine=$ordre['Magazine'];
            $numeros=$l->collection[$pays][$magazine];
            if ($get_html === true)
                sort($numeros);
            $total_numeros+=count($numeros);
            list($magazine, $numeros_clean_et_references) = self::get_numeros_clean($pays, $magazine, $numeros);
            foreach($numeros_clean_et_references as $numero) {
                if (!array_key_exists('clean', $numero)) {
                    $numero['clean'] = $numero['reference'];
                }
                $e=new Edge($pays, $magazine, $numero['clean'],$numero['reference']);

                if ($get_html) {
                    $texte_final.=$e->html;
                }
                if ($e->est_visible)
                    $total_numeros_visibles++;
            }
        }
        $pourcentage_visible=$total_numeros==0 ? 0 : intval(100*$total_numeros_visibles/$total_numeros);
        if ($get_html)
            return [$texte_final, $pourcentage_visible];
        else
            return $pourcentage_visible;
	}

    static function getParametresBibliotheque($id_user) {
        $textures = [];
        for ($i = 1; $i <= 2; $i++) {
            $requete_textures = 'SELECT Bibliotheque_Texture' . $i . ', Bibliotheque_Sous_Texture' . $i . ' FROM users WHERE ID = \'' . $id_user . '\'';
            $resultat_textures = DM_Core::$d->requete_select_distante($requete_textures);
            $textures[] = $resultat_textures[0]['Bibliotheque_Texture' . $i];
            $textures[] = $resultat_textures[0]['Bibliotheque_Sous_Texture' . $i];
        }
    }

	function getChemin() {
		return 'edges/'.$this->pays.'/elements';
	}

}
DM_Core::$d->requete_distante('SET NAMES UTF8');
if (isset($_POST['get_visible'])) {
    $est_partage_bibliotheque = $_POST['est_partage_bibliotheque'];
	include_once ('locales/lang.php');
	$nom_complet_magazine=Inducks::get_nom_complet_magazine($_POST['pays'], $_POST['magazine']);
	?>
	<div class="titre_magazine"><?=$nom_complet_magazine?></div><br />
	<div class="numero_magazine">n&deg;<?=$_POST['numero']?></div><br />
	<?php
	if (!getEstVisible($_POST['pays'], strtoupper($_POST['magazine']), $_POST['numero'])) {
		?>
        <?=TRANCHE_NON_DISPONIBLE1?><br /><?php
        if (!$est_partage_bibliotheque) {
            ?><?= TRANCHE_NON_DISPONIBLE2 ?>
            <a class="lien_participer" target="_blank"
               href="?action=bibliotheque&onglet=participer"><?= ICI ?></a><?= TRANCHE_NON_DISPONIBLE3 ?>
        <?php
        }
	}
	?>
    <div style="position:absolute;width:100%;text-align:center;border-top:1px solid black;bottom:10px"><?=DECOUVRIR_COUVERTURE?></div><?php
}
elseif (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numero'])) {
	if (isset($_GET['grossissement']))
		Edge::$grossissement_affichage=$_GET['grossissement'];
	if (!isset($_GET['debug']))
		header('Content-type: image/png');
	$e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero'],$_GET['numero'],true);
	imagepng($e->image);
}
elseif (isset($_POST['get_bibliotheque'])) {
    header('Content-type: application/json');

    $user = $_POST['user_bibliotheque'];
    $cle = $_POST['cle_bibliotheque'];
    if ($user !== '-1') {
        $id_user = DM_Core::$d->get_id_user_partage_bibliotheque($user, $cle);
        $titre = BIBLIOTHEQUE_DE.$user;
    }
    else {
        $id_user = DM_Core::$d->user_to_id($_SESSION['user']);
        $titre = BIBLIOTHEQUE_COURT;
    }

    if (is_null($id_user)) {
        echo json_encode(['erreur' => 'Lien de partage invalide']);
    }
    else {
        $requete_grossissement = 'SELECT username, Bibliotheque_Grossissement FROM users WHERE ID = \'' . $id_user . '\'';
        $resultat_grossissement = DM_Core::$d->requete_select_distante($requete_grossissement);
        $username = $resultat_grossissement[0]['username'];
        $grossissement = $resultat_grossissement[0]['Bibliotheque_Grossissement'];

        $textures = [];
        for ($i = 1; $i <= 2; $i++) {
            $requete_textures = 'SELECT Bibliotheque_Texture' . $i . ', Bibliotheque_Sous_Texture' . $i . ' FROM users WHERE ID = \'' . $id_user . '\'';
            $resultat_textures = DM_Core::$d->requete_select_distante($requete_textures);
            $textures[$i - 1] = [
                'texture' => $resultat_textures[0]['Bibliotheque_Texture' . $i],
                'sous_texture' => $resultat_textures[0]['Bibliotheque_Sous_Texture' . $i]
			];
        }

        Edge::$grossissement = $grossissement;
        Etagere::$largeur = $_POST['largeur'];
        Etagere::$hauteur = $_POST['hauteur'];
        Etagere::$epaisseur = 20;
        Etagere::$texture1 = $textures[0]['texture'];
        Etagere::$sous_texture1 = $textures[0]['sous_texture'];
        Etagere::$texture2 = $textures[1]['texture'];
        Etagere::$sous_texture2 = $textures[1]['sous_texture'];

        ob_start();
        include_once('edgetest.php');
        $contenu = ob_get_clean();

        echo json_encode(['titre' => $titre, 'contenu' => $contenu, 'textures' => $textures]);
    }
}

/*
 * Table bibliotheque_options
*/
elseif (isset($_POST['get_texture'])) {
	$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
	$requete_texture='SELECT Bibliotheque_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select_distante($requete_texture);
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
	$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
	$requete_texture='SELECT Bibliotheque_Sous_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select_distante($requete_texture);

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
elseif (isset($_POST['partager_bibliotheque'])) {
    Affichage::partager_page();
}
elseif (isset($_POST['generer_image'])) {
	error_reporting(E_ALL);
	$nom_fichier='edges/_tmp/'.$_SESSION['user'].'-'.md5($_SESSION['user']).'.jpg';
	$images= ['texture1','sous_texture1','texture2','sous_texture2'];
	$variables= ['largeur','texture1','sous_texture1','texture2','sous_texture2'];
	foreach($variables as $variable)
		${$variable}=$_POST[$variable];
	
	$largeur=intval($largeur)-20;

	$image_texture1=imagecreatefromjpeg('edges/textures/'.$texture1.'/'.$sous_texture1.'.jpg');
	$image_texture2=imagecreatefromjpeg('edges/textures/'.$texture2.'/'.$sous_texture2.'.jpg');
	$pos=json_decode(str_replace('\"','"',$_POST['pos']));
	
	/*$xml='<xml>'."\n"
		.'<texture1>'.'edges/textures/'.$texture1.'/'.$sous_texture1.'.jpg'.'</texture1>'."\n"
		.'<texture2>'.'edges/textures/'.$texture2.'/'.$sous_texture2.'.jpg'.'</texture2>'."\n"
		.'<largeur>'.$largeur.'</largeur>'."\n"
		.jsonToXML($pos)
		.'</xml>';*/
	$contenu=implode("\n", [$texture1.'/'.$sous_texture1,$texture2.'/'.$sous_texture2,$largeur,str_replace('\"','"',$_POST['pos'])]);
	Util::ecrire_dans_fichier('edges/_tmp/'.$_SESSION['user'].'-'.md5($_SESSION['user']).'.json', $contenu, false);
	include_once('ServeurDb.class.php');
	?>
	<a style="float:left;border-bottom:1px dashed white" target="_blank" href="javascript:void(0)"
	   onclick="window.open('<?= ServeurDb::getRemoteUrl('Merge.class.php') ?>?user=<?=$_SESSION['user']?>-<?=md5($_SESSION['user'])?>','Download')">
		<?=BIBLIOTHEQUE_SAUVEGARDER_IMAGE?>
	</a><?php
}
elseif (isset($_POST['generer_images_etageres'])) {
	error_reporting(E_ALL);
	$images= ['texture1','sous_texture1','texture2','sous_texture2'];
	$variables= ['largeur','texture1','sous_texture1','texture2','sous_texture2'];
	foreach($variables as $variable)
		${$variable}=$_POST[$variable];
		
	$largeur=intval($largeur)-20;

	$image_texture1=imagecreatefromjpeg('edges/textures/'.$texture1.'/'.$sous_texture1.'.jpg');
	$image_texture2=imagecreatefromjpeg('edges/textures/'.$texture2.'/'.$sous_texture2.'.jpg');
	$pos=json_decode(str_replace('\"','"',$_POST['pos']));
	foreach($pos as $type_element=>$pos_elements) {
		foreach($pos_elements as $i=>$pos_element) {
			$pos->$type_element->$i=explode('-',$pos->$type_element->$i);
		}
	}
	$max_y=0;
	$pos_sup_gauche= [];
	foreach($pos->etageres->etageres as $i=>$pos_etagere) {
		$pos_etagere_courante=explode(',',$pos_etagere);
		if ($i==0)
			$pos_sup_gauche=$pos_etagere_courante;
		if ($pos_etagere_courante[1] > $max_y)
			$max_y=$pos_etagere_courante[1];
	}
	$min_y=$pos_sup_gauche[1];
	
	$id_premiere_tranche=0;
	foreach($pos->etageres->etageres as $num_etagere=>$pos_etagere) {
		$pos_etagere_courante=explode(',',$pos_etagere);
		if (isset($pos->etageres->etageres[$num_etagere+1]))
			$pos_etagere_suivante=explode(',',$pos->etageres->etageres[$num_etagere+1]);
		else
			$pos_etagere_suivante=explode(',',$pos->etageres->etageres[$num_etagere]);
	   	$hauteur=$pos_etagere_suivante[1]-$pos_etagere_courante[1];
	   	if ($hauteur ==0) // Cas de la dernière étagère, vide
	   		$hauteur=16;
		$im=imagecreatetruecolor($largeur, $hauteur);
		
		for ($i=0;$i<$largeur;$i+=imagesx($image_texture1))
			for ($j=0;$j<$hauteur;$j+=imagesy($image_texture1))
				imagecopy ($im, $image_texture1, $i, $j, 0, 0, imagesx($image_texture1), imagesy($image_texture1));
			imagecopyresampled($im, $image_texture2, 0, 0, 0, 0, $largeur, 16, imagesx($image_texture2), 16);
		foreach($pos->tranches as $src_tranche=>$pos_tranches) {
			$image_tranche=imagecreatefrompng(preg_replace('#\?.*#is', '', $src_tranche));
			foreach($pos_tranches as $pos_tranche) {
				$pos_courante=explode(',',$pos_tranche);
				if ($pos_courante[1]-$pos_sup_gauche[1]+$pos_courante[3] > $pos_etagere_courante[1]+$hauteur) {
			   		continue;
				}
				imagecopyresampled($im, $image_tranche, $pos_courante[0]-$pos_sup_gauche[0], $pos_courante[1]-$pos_etagere_courante[1], 0, 0, $pos_courante[2], $pos_courante[3], imagesx($image_tranche), imagesy($image_tranche));   
			}
			imagedestroy($image_tranche);
		}
		$nom_fichier='edges/_tmp/'.$_SESSION['user'].'-'.md5($_SESSION['user']).'-'.$num_etagere.'.jpg';
		imagejpeg($im,$nom_fichier);
		imagedestroy($im);
	}
	
	?><a style="float:left;border-bottom:1px dashed white" target="_blank" href="<?= ServeurDb::getRemoteUrl('Merge.class.php') ?>?user=<?=$_SESSION['user']?>-<?=md5($_SESSION['user'])?>&nb=<?=count($pos->etageres->etageres)?>&largeur=<?=$largeur?>">
		<?=BIBLIOTHEQUE_SAUVEGARDER_IMAGE?>
	</a>
   	<?php
	
}

function getEstVisible($pays,$magazine,$numero, $get_html=false, $regen=false) {
	$e=new Edge();
	$e->pays=$pays;
	$e->magazine=$magazine;
	$e->numero=$numero;
	$requete_est_visible='SELECT issuenumber FROM tranches_pretes WHERE publicationcode = \''.($pays.'/'.$magazine).'\' AND issuenumber = \''.$numero.'\'';
	$e->est_visible=count(DM_Core::$d->requete_select_distante($requete_est_visible)) > 0;
		
	if ($get_html)
		return [$e->getImgHTML($regen),$e->est_visible];
	else
		return $e->est_visible;
}

function imagecreatefrompng_getimagesize($chemin) {
	$image=imagecreatefrompng($chemin);
	return [$image,imagesx($image),imagesy($image)];
}

function imagecreatefromgif_getimagesize($chemin) {
	$image=imagecreatefromgif($chemin);
	return [$image,imagesx($image),imagesy($image)];
}

if (!function_exists('imagepalettetotruecolor')) {
	function imagepalettetotruecolor(&$img) {
		if (!imageistruecolor($img))
		{
			$w = imagesx($img);
			$h = imagesy($img);
			$img1 = imagecreatetruecolor($w,$h);
			imagecopy($img1,$img,0,0,0,0,$w,$h);
			$img = $img1;
		}
	}
}

function rgb2hex($r,$g,$b) {
	$hex = "";
	$rgb= [$r,$g,$b];
	for ($i = 0; $i < 3; $i++) {
		if (($rgb[$i] > 255) || ($rgb[$i] < 0)) {
			echo "Error : input must be between 0 and 255";
			return 0;
		}
		$tmp = dechex($rgb[$i]);
		if (strlen($tmp) < 2)
			$hex .= "0" . $tmp;
		else
			$hex .= $tmp;
	}
	return $hex;
}


function remplacerCouleur(&$im,$r_old,$g_old,$b_old,$r,$g,$b) {
	if ($r_old===$r && $g_old===$g && $b_old===$b)
		return;
	$width = imagesx($im);
	$height = imagesy($im);
	$oldhex = rgb2hex($r_old,$g_old,$b_old);
	$hex = rgb2hex($r,$g,$b);
	$color = imagecolorallocate($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 6)));
	for ($cloneH = 0; $cloneH < $height; $cloneH++) {
		for ($x = 0; $x < $width; $x++) {
			if (colormatch($im, $x, $cloneH, $oldhex))
			   imagesetpixel($im, $x, $cloneH, $color);
	   }
   }
}

function colormatch($image, $x, $y, $hex) {
	$rgb = imagecolorat($image, $x, $y);
	$r = ($rgb >> 16) & 0xFF;
	$g = ($rgb >> 8) & 0xFF;
	$b = $rgb & 0xFF;

	$r2 = hexdec(substr($hex, 0, 2));
	$g2 = hexdec(substr($hex, 2, 2));
	$b2 = hexdec(substr($hex, 4, 6));
	return $r == $r2 && $b == $b2 && $g == $g2;
}

function get_hauteur($image) {
	return imagesy($image);
}
?>
