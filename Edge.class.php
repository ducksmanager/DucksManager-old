<?php
include_once('Texte.class.php');
include_once('IntervalleValidite.class.php');
include_once('DucksManager_Core.class.php');
include_once('getDimensions.php');
include_once('Etagere.class.php');

class Edge {
	var $pays;
	var $magazine;
	var $numero;
	var $numero_reference;
	var $textes=array();
	var $largeur=20;
	var $hauteur=200;
	var $image;
	var $image_existe;
	var $o;
	var $est_visible=true;
	var $magazine_est_inexistant=false;
	var $intervalles_validite=array();
	var $en_cours=array();
	var $html='';
	static $grossissement=10;
	static $grossissement_affichage=1.5;
	static $grossissement_defaut=1.5;
	static $largeur_numeros_precedents=0;
	static $d;
	
	function Edge($pays=null,$magazine=null,$numero=null,$numero_reference=null,$image_seulement=false) {
		if (is_null($pays))
			return;
		$this->magazine_est_inexistant=false;
		$this->pays=$pays;
		$this->magazine=$magazine;
		$this->numero= $numero;
		$this->numero_reference= $numero_reference;
		
		$url_image='edges/'.$this->pays.'/gen/'.$this->magazine.'.'.$this->numero_reference.'.png';
		if ($image_seulement)
			$this->image=imagecreatefrompng($url_image);
		else {
			$this->est_visible=getEstVisible($this->pays,$this->magazine,$this->numero_reference);
			
			$this->image_existe=file_exists($url_image);
			if ($this->image_existe) {
				if (! (list($this->largeur,$this->hauteur,$type,$attr)=@getimagesize($url_image))) {
					mail('admin@ducksmanager.net', 'Image de biblioth�que corrompue',$url_image);
					return;
				}
				$this->largeur=intval( $this->largeur / (Edge::$grossissement_affichage/Edge::$grossissement_defaut));
				$this->hauteur=intval( $this->hauteur / (Edge::$grossissement_affichage/Edge::$grossissement_defaut));
				
			}
			else {
				$dimensions=getDimensionsParDefautMagazine($this->pays,$this->magazine,array($this->numero_reference));
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
		return array($this->largeur,$this->hauteur);
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
		$numeros_clean_et_references=array();
		foreach($numeros as $i=>$numero) {
			$numero=$numero[0];
			$numero_clean=self::get_numero_clean($numero);
			$numeros[$i]="'".$numero_clean."'";
			$numeros_clean_et_references[$numero]=array('clean'=>$numero_clean,'reference'=>$numero_clean);
		}
		
		$numeros_subarrays=array_chunk($numeros, 250);
		
		foreach($numeros_subarrays as $numeros_subarray) {
			$requete_recherche_numero_reference=
				'SELECT Numero,NumeroReference '
			   .'FROM tranches_doublons '
			   .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero IN ('.implode(',', $numeros_subarray).') ';
			$resultat_numero_reference=DM_Core::$d->requete_select($requete_recherche_numero_reference);
			foreach($resultat_numero_reference as $numero_reference) {
				$numeros_clean_et_references[$numero_reference['Numero']]['reference']
					=$numero_reference['NumeroReference'];
			}
		}

		$vrai_magazine=Inducks::get_vrai_magazine($pays,$magazine);
		if ($vrai_magazine != $magazine) {
			foreach($numeros_clean_et_references as $i=>$numero) {
				$numero_clean = is_array($numero) ? $numero['clean'] : $numero;
				list($vrai_magazine,$vrai_numero) = Inducks::get_vrais_magazine_et_numero($pays,$magazine,$numero_clean);
				$numeros_clean_et_references[$numero_clean]['reference']=$vrai_numero;
			}
			$magazine=$vrai_magazine;
		}

		return array($vrai_magazine, $numeros_clean_et_references);
	}
	
	function getImgHTML($regen=false) {
		$code='';
		if (Edge::$largeur_numeros_precedents + $this->largeur > Etagere::$largeur) {
			$code.=Edge::getEtagereHTML();
			Edge::$largeur_numeros_precedents=0;
		}
		if ($this->hauteur > Etagere::$hauteur_max_etage)
			Etagere::$hauteur_max_etage = $this->hauteur ;
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

	function placer_image($sous_image, $position='haut', $decalage=array(0,0), $compression_largeur=1, $compression_hauteur=1) {
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

	static function getPourcentageVisible($get_html=false, $regen=false, $user_unique=true) {
		include_once('Database.class.php');
		global $numeros_inducks;
		@session_start();
		if ($user_unique===true)
			$ids_users=array(DM_Core::$d->user_to_id($_SESSION['user']));
		else {
			$pourcentages_visible=array();
			$requete_users='SELECT ID, username FROM users';
			$resultat_users=DM_Core::$d->requete_select($requete_users);
			foreach($resultat_users as $user)
				$ids_users[$user['username']]=$user['ID'];
		}
		foreach($ids_users as $username=>$id_user) {
			$l=DM_Core::$d->toList($id_user);
			$texte_final='';
			$total_numeros=0;
			$total_numeros_visibles=0;
			DM_Core::$d->maintenance_ordre_magazines($id_user);
			$requete_ordre_magazines='SELECT Pays,Magazine,Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' ORDER BY Ordre';
			$resultat_ordre_magazines=DM_Core::$d->requete_select($requete_ordre_magazines);
			$publication_codes=array();
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
					$e=new Edge($pays, $magazine, $numero['clean'],$numero['reference']);
					
					if ($get_html) {
						$texte_final.=$e->html;
					}
					if ($e->est_visible)
						$total_numeros_visibles++;
				}
			}
			$pourcentage_visible=$total_numeros==0 ? 0 : intval(100*$total_numeros_visibles/$total_numeros);
			if ($user_unique===true) {
				if ($get_html)
					return array($texte_final, $pourcentage_visible);
				else
					return $pourcentage_visible;
			}
			elseif ($total_numeros>0)
				$pourcentages_visible[' '.$username]=$pourcentage_visible;
		}
		return $pourcentages_visible;
	}

	function getChemin() {
		return 'edges/'.$this->pays.'/elements';
	}

}
DM_Core::$d->requete('SET NAMES UTF8');
if (isset($_POST['get_visible'])) {
	include_once ('locales/lang.php');
	list($nom_complet_pays,$nom_complet_magazine)=$nom_complet_magazine=Inducks::get_nom_complet_magazine($_POST['pays'], $_POST['magazine']);
	?>
	<div class="titre_magazine"><?=$nom_complet_magazine?></div><br />
	<div class="numero_magazine">n&deg;<?=$_POST['numero']?></div><br />
	<?php
	if (!getEstVisible($_POST['pays'], strtoupper($_POST['magazine']), $_POST['numero'])) {
		?>
		<?=TRANCHE_NON_DISPONIBLE1?><br /><?=TRANCHE_NON_DISPONIBLE2?><a class="lien_participer" target="_blank" href="?action=bibliotheque&onglet=participer"><?=ICI?></a><?=TRANCHE_NON_DISPONIBLE3?>
		<?php
	}
	?>
		<div style="position:absolute;width:100%;text-align:center;border-top:1px solid black;bottom:10px"><?=DECOUVRIR_COUVERTURE?></div>
	<?php
}
elseif (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numero'])) {
	if (isset($_GET['grossissement']))
		Edge::$grossissement_affichage=$_GET['grossissement'];
	if (!isset($_GET['debug']))
		header('Content-type: image/png');
	$e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero'],$_GET['numero'],true);
	imagepng($e->image);
}
/*
 * Table bibliotheque_options
*/
elseif (isset($_POST['get_texture'])) {
	$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
	$requete_texture='SELECT Bibliotheque_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select($requete_texture);
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
	$resultat_texture=DM_Core::$d->requete_select($requete_texture);

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
elseif (isset($_POST['generer_image'])) {
	error_reporting(E_ALL);
	$nom_fichier='edges/_tmp/'.$_SESSION['user'].'-'.md5($_SESSION['user']).'.jpg';
	$images=array('texture1','sous_texture1','texture2','sous_texture2');
	$variables=array('largeur','texture1','sous_texture1','texture2','sous_texture2');
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
	$contenu=implode("\n",array($texture1.'/'.$sous_texture1,$texture2.'/'.$sous_texture2,$largeur,str_replace('\"','"',$_POST['pos'])));
	Util::ecrire_dans_fichier('edges/_tmp/'.$_SESSION['user'].'-'.md5($_SESSION['user']).'.json', $contenu, false);
	include_once('_priv/Database.priv.class.php');
	?>
	<a style="float:left;border-bottom:1px dashed white" target="_blank" href="javascript:void(0)"
	   onclick="window.open('<?=Database::get_remote_url('Merge.class.php')?>?user=<?=$_SESSION['user']?>-<?=md5($_SESSION['user'])?>','Download')">
		<?=BIBLIOTHEQUE_SAUVEGARDER_IMAGE?>
	</a><?php
}
elseif (isset($_POST['generer_images_etageres'])) {
	error_reporting(E_ALL);
	$images=array('texture1','sous_texture1','texture2','sous_texture2');
	$variables=array('largeur','texture1','sous_texture1','texture2','sous_texture2');
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
	$pos_sup_gauche=array();
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
	   	if ($hauteur ==0) // Cas de la derni�re �tag�re, vide
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
	
	?><a style="float:left;border-bottom:1px dashed white" target="_blank" href="<?=Database::get_remote_url('Merge.class.php')?>?user=<?=$_SESSION['user']?>-<?=md5($_SESSION['user'])?>&nb=<?=count($pos->etageres->etageres)?>&largeur=<?=$largeur?>">
		<?=BIBLIOTHEQUE_SAUVEGARDER_IMAGE?>
	</a>
   	<?php
	
}
elseif (isset($_GET['dispo_tranches'])) {
	$data=Edge::getPourcentageVisible(false, false, false);
	asort($data);
	$usernames=array_keys($data);
	sort($data);
	$somme=0;
	foreach($data as $pct)
		$somme+=$pct;
	$moyenne=$somme/count($data);
	$data_moyenne=array();
	for($i=0;$i<count($data);$i++)
		$data_moyenne[]=$moyenne;

	include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';

	$chart = new open_flash_chart();
	$chart->set_title( new title( 'Disponibilite des tranches' ) );

	//
	// Make our area chart:
	//
	$area = new area();
	// set the circle line width:
	$area->set_width( 2 );
	$area->set_default_dot_style( new hollow_dot() );
	$area->set_colour( '#838A96' );
	$area->set_fill_colour( '#E01B49' );
	$area->set_fill_alpha( 0.4 );
	$area->set_values( $data );
	$t=new tooltip( "Utilisateur #x_label<br>#val#" );

	// add the area object to the chart:
	$chart->add_element( $area );
	
	$line_dot = new line();
	$line_dot->set_values($data_moyenne);
	$line_dot->set_tooltip( $t );
	$chart->add_element( $line_dot );

	$y_axis = new y_axis();
	$y_axis->set_range( 0, 100, 10 );
	$y_axis->labels = null;
	$y_axis->set_offset( false );

	$chart->add_y_axis( $y_axis );

	$x_labels = new x_axis_labels();
	$x_labels->set_vertical();
	$x_labels->set_colour( '#A2ACBA' );
	$x_labels->set_labels($usernames);
	
	$x = new x_axis();
	$x->set_colour( '#A2ACBA' );
	$x->set_grid_colour( '#D7E4A3' );
	$x->set_offset( false );
	$x->set_labels( $x_labels );
	
	$chart->set_x_axis( $x );

	?>
		<html>
			<head>
			<link rel="stylesheet" type="text/css" href="style.css">
			<!--[if IE]>
					<style type="text/css" media="all">@import "fix-ie.css";</style>
			<![endif]-->
			<script type="text/javascript" src="js/json/json2.js"></script>
			<script type="text/javascript" src="js/swfobject.js"></script>
			<script type="text/javascript">
			swfobject.embedSWF("open-flash-chart.swf", "my_chart", "<?=(25*count($usernames))?>", "380", "9.0.0");
			</script>

			<script type="text/javascript">

			function open_flash_chart_data()
			{
				return JSON.stringify(data);
			}

			function findSWF(movieName) {
			  if (navigator.appName.indexOf("Microsoft")!= -1) {
				return window[movieName];
			  } else {
				return document[movieName];
			  }
			}

			var data = <?php echo $chart->toPrettyString(); ?>;

			</script>
			</head>
			<body>
				<div id="my_chart"></div>
			</body>
		</html>
		<?php
}

function getEstVisible($pays,$magazine,$numero, $get_html=false, $regen=false) {
	$e=new Edge();
	$e->pays=$pays;
	$e->magazine=$magazine;
	$e->numero=$numero;
	$requete_est_visible='SELECT issuenumber FROM tranches_pretes WHERE publicationcode = \''.($pays.'/'.$magazine).'\' AND issuenumber = \''.$numero.'\'';
	$e->est_visible=count(DM_Core::$d->requete_select($requete_est_visible)) > 0;
		
	if ($get_html)
		return array($e->getImgHTML($regen),$e->est_visible);
	else
		return $e->est_visible;
}

function imagecreatefrompng_getimagesize($chemin) {
	$image=imagecreatefrompng($chemin);
	return array($image,imagesx($image),imagesy($image));
}

function imagecreatefromgif_getimagesize($chemin) {
	$image=imagecreatefromgif($chemin);
	return array($image,imagesx($image),imagesy($image));
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
	$rgb=array($r,$g,$b);
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
	$cloneH = 0;
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
