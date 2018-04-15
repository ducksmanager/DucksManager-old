<?php
include_once 'IntervalleValidite.class.php';
include_once 'DucksManager_Core.class.php';
include_once 'getDimensions.php';
include_once 'Etagere.class.php';

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

    function __construct($pays = null, $magazine = null, $numero = null, $numero_reference = null, $visible = null, $image_seulement = false) {
		if (is_null($pays)) {
            return;
        }
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
            $this->est_visible = $visible ?? getEstVisible($this->pays,$this->magazine,$this->numero_reference);

			$this->image_existe=file_exists($url_image);
			if ($this->image_existe) {
				if (! (list($this->largeur,$this->hauteur,,)=@getimagesize($url_image))) {
					mail('admin@ducksmanager.net', 'Image de bibliothèque corrompue',$url_image);
					return;
				}
				$this->largeur= (int)($this->largeur / (Edge::$grossissement_affichage / Edge::$grossissement_defaut));
				$this->hauteur= (int)($this->hauteur / (Edge::$grossissement_affichage / Edge::$grossissement_defaut));

			}
			else {
				$dimensions=getDimensionsParDefautMagazine($this->pays,$this->magazine, [$this->numero_reference]);
				if ($dimensions[$this->numero_reference] !== 'null') {
                    list($this->largeur, $this->hauteur) = explode('x', $dimensions[$this->numero_reference]);
                }

				@imagepng($this->dessiner_defaut(),$url_image);

				$this->est_visible=false;
				$this->largeur*= self::$grossissement_affichage;
				$this->hauteur*= self::$grossissement_affichage;

				$this->magazine_est_inexistant=true;
			}
			$this->html=$this->getImgHTML();
		}
        $this->visible = $visible;
    }

	function getLargeurHauteurDefaut() {
		return [$this->largeur,$this->hauteur];
	}
	
	static function getEtagereHTML($br=true) {
		$code= '<div class="etagere" style="width:'.Etagere::$largeur.';'
										  .'background-image: url(\'edges/textures/'.Etagere::$texture2.'/'.Etagere::$sous_texture2.'.jpg\')">&nbsp;</div>';
		if ($br===true) {
            $code .= '<br />';
        }
		return $code;
	}

	static function get_numero_clean($numero) {
		return str_replace([' ', '+'], '', $numero);
	}
	
	
	static function get_numeros_clean($pays,$magazine,$numeros) {
        $chunk_size = 250;

		$numeros_clean_et_references= [];
		foreach($numeros as $i=>$numero) {
			$numero=$numero[0];
			$numero_clean=self::get_numero_clean($numero);
			$numeros[$i]="'".$numero_clean."'";
			$numeros_clean_et_references[$numero]= [
                'clean'=>$numero_clean,
                'reference'=>$numero_clean,
                'visible' => false
            ];
		}
		
		$numeros_subarrays=array_chunk($numeros, $chunk_size);
		
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
		if ($vrai_magazine !== $magazine) {
			foreach($numeros_clean_et_references as $numero) {
				$numero_clean = is_array($numero) ? $numero['clean'] : $numero;
				list($vrai_magazine,$vrai_numero) = Inducks::get_vrais_magazine_et_numero($pays,$magazine,$numero_clean);
				$numeros_clean_et_references[$numero_clean]['reference']=$vrai_numero;
			}
		}

        $numeros_references = array_map(function ($numero) {
            return $numero['reference'];
        }, $numeros_clean_et_references);

        $requete_visibilite_numeros = "
              SELECT issuenumber
              FROM tranches_pretes
              WHERE publicationcode = '$pays/$magazine'
                AND issuenumber IN ('" . implode("', '", $numeros_references) . "')";
        $resultat_visibilite_numeros = DM_Core::$d->requete_select($requete_visibilite_numeros);

        foreach($resultat_visibilite_numeros as $numero) {
            array_walk($numeros_clean_et_references, function(&$value) use($numero) {
                if ($value['reference'] === $numero['issuenumber']) {
                    $value['visible'] = true;
                }
            });
        }

		return [$vrai_magazine, $numeros_clean_et_references];
	}
	
	function getImgHTML($regen=false) {
		$code='';
		if (!self::$sans_etageres) {
			if (self::$largeur_numeros_precedents + $this->largeur > Etagere::$largeur) {
				$code .= self::getEtagereHTML();
				self::$largeur_numeros_precedents = 0;
			}
			if ($this->hauteur > Etagere::$hauteur_max_etage) {
				Etagere::$hauteur_max_etage = $this->hauteur;
			}
		}
		$code.= '<img data-edge="'.($this->est_visible ? 1 : 0).'" class="tranche" ';
		
		if ($this->image_existe && !$regen) {
			$code.='name="'.$this->pays.'/'.$this->magazine.'.'.$this->numero_reference.'" ';
		}
		else {
			$code.='name="Edge.class.php?pays='.$this->pays.'&amp;magazine='.$this->magazine.'&amp;numero='.$this->numero_reference.'&amp;grossissement='. self::$grossissement.'" ';
		}
		$code.='width="'.$this->largeur.'" height="'.$this->hauteur.'" id="'.$this->pays.'/'.$this->magazine.'.'.$this->numero.'"/>';
		
		self::$largeur_numeros_precedents+=$this->largeur;
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
		if (function_exists('imageantialias')) {
		    imageantialias($this->image, true);
        }
		imagefilledrectangle($this->image, $this->largeur/4,$this->largeur/4, $this->largeur*3/4,$this->largeur*3/4,$gris_250);
		return $this->image;
	}

    function dessiner_contour() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        for ($i=0; $i<.15* self::$grossissement; $i++) {
            imagerectangle($this->image, $i, $i, $this->largeur - 1 - $i, $this->hauteur - 1 - $i, $noir);
        }
    }

    static function getPointsPhotographeAGagner($id_user){
        $requete_points_tranche = "
            SELECT np.Pays, np.Magazine, np.Numero, np.Popularite
            FROM numeros
            INNER JOIN numeros_popularite np USING(Pays, Magazine, Numero)
            WHERE numeros.ID_Utilisateur=$id_user
            ORDER BY np.Popularite DESC
        ";

        $resultats_points_tranches = DM_Core::$d->requete_select($requete_points_tranche);
        return array_map(function($tranche) {
            $tranche['Popularite'] = (int)$tranche['Popularite'];
            return $tranche;
        }, $resultats_points_tranches);
    }

	static function getPourcentageVisible($id_user, $get_html=false) {
		include_once 'Database.class.php';
		@session_start();

        $l=DM_Core::$d->toList($id_user);
        $texte_final='';
        $total_numeros=0;
        $total_numeros_visibles=0;
        DM_Core::$d->maintenance_ordre_magazines($id_user);

        // TODO Use DM server service
        $requete_ordre_magazines='SELECT Pays,Magazine,Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' ORDER BY Ordre';
        $resultat_ordre_magazines=DM_Core::$d->requete_select($requete_ordre_magazines);
        $publication_codes= [];
        foreach($resultat_ordre_magazines as $ordre) {
            $pays=$ordre['Pays'];
            $magazine=$ordre['Magazine'];
            $publication_codes[]=$pays.'/'.$magazine;
        }

        global $numeros_inducks;
        $numeros_inducks = Inducks::get_liste_numeros_from_publicationcodes($publication_codes);
        getDimensionsParDefaut($publication_codes);

        foreach($resultat_ordre_magazines as $ordre) {
            $pays=$ordre['Pays'];
            $magazine=$ordre['Magazine'];
            $numeros=$l->collection[$pays][$magazine];
            if ($get_html === true) {
                sort($numeros);
            }
            $total_numeros+=count($numeros);

            list($magazine, $numeros_clean_et_references) = self::get_numeros_clean($pays, $magazine, $numeros);
            foreach($numeros_clean_et_references as $numero) {
                if (!array_key_exists('clean', $numero)) {
                    $numero['clean'] = $numero['reference'];
                }
                $e=new Edge($pays, $magazine, $numero['clean'], $numero['reference'], $numero['visible'], false);

                if ($get_html) {
                    $texte_final.=$e->html;
                }
                if ($e->est_visible) {
                    $total_numeros_visibles++;
                }
            }
        }
        $pourcentage_visible=$total_numeros===0 ? 0 : (int)(100 * $total_numeros_visibles / $total_numeros);
        if ($get_html) {
            return [$texte_final, $pourcentage_visible, Inducks::get_noms_complets_magazines($publication_codes)];
        }
        else {
            return $pourcentage_visible;
        }
	}

    static function getParametresBibliotheque($id_user) {
        $textures = [];
        for ($i = 1; $i <= 2; $i++) {
            $requete_textures = 'SELECT Bibliotheque_Texture' . $i . ', Bibliotheque_Sous_Texture' . $i . ' FROM users WHERE ID = \'' . $id_user . '\'';
            $resultat_textures = DM_Core::$d->requete_select($requete_textures);
            $textures[] = $resultat_textures[0]['Bibliotheque_Texture' . $i];
            $textures[] = $resultat_textures[0]['Bibliotheque_Sous_Texture' . $i];
        }
    }

    static function get_user_bibliotheque($user, $cle) {
        if ($user === '-1') {
            $id_user = $_SESSION['id_user'];
        }
        else {
            $id_user = DM_Core::$d->get_id_user_partage_bibliotheque($user, $cle);
        }
        return $id_user;
    }

	function getChemin() {
		return 'edges/'.$this->pays.'/elements';
	}

}
DM_Core::$d->requete('SET NAMES UTF8');
if (isset($_POST['get_points'])) {
    $nb_points_courants = DM_Core::$d->get_points_courants($_SESSION['id_user']);
    echo json_encode(['points' => $nb_points_courants]);
}
elseif (isset($_GET['pays'], $_GET['magazine'], $_GET['numero'])) {
	if (isset($_GET['grossissement'])) {
        Edge::$grossissement_affichage = $_GET['grossissement'];
    }
	if (!isset($_GET['debug'])) {
        header('Content-type: image/png');
    }
	$e=new Edge($_GET['pays'], $_GET['magazine'], $_GET['numero'], $_GET['numero'], null, true);
	imagepng($e->image);
}
elseif (isset($_POST['get_popularite_numeros'])) {
    header('Content-type: application/json');
    echo json_encode([
        'popularite_numeros' => Edge::getPointsPhotographeAGagner($_SESSION['id_user'])
    ]);
}
elseif (isset($_POST['get_bibliotheque'])) {
    header('Content-type: application/json');
    $user = $_POST['user_bibliotheque'];
    $id_user = Edge::get_user_bibliotheque($user, $_POST['cle_bibliotheque']);

    if (is_null($id_user)) {
        echo json_encode(['erreur' => 'Lien de partage invalide']);
    }
    else {
        $requete_grossissement = 'SELECT username, Bibliotheque_Grossissement FROM users WHERE ID = \'' . $id_user . '\'';
        $resultat_grossissement = DM_Core::$d->requete_select($requete_grossissement);
        $username = $resultat_grossissement[0]['username'];
        $grossissement = $resultat_grossissement[0]['Bibliotheque_Grossissement'];

        $textures = [];
        for ($i = 1; $i <= 2; $i++) {
            $requete_textures = 'SELECT Bibliotheque_Texture' . $i . ', Bibliotheque_Sous_Texture' . $i . ' FROM users WHERE ID = \'' . $id_user . '\'';
            $resultat_textures = DM_Core::$d->requete_select($requete_textures);
            $textures[$i - 1] = [
                'texture' => $resultat_textures[0]['Bibliotheque_Texture' . $i],
                'sous_texture' => $resultat_textures[0]['Bibliotheque_Sous_Texture' . $i]
			];
        }

        Etagere::$texture1 = $textures[0]['texture'];
        Etagere::$sous_texture1 = $textures[0]['sous_texture'];
        Etagere::$texture2 = $textures[1]['texture'];
        Etagere::$sous_texture2 = $textures[1]['sous_texture'];
        Edge::$grossissement = $grossissement;

        list($width, $height, $type, $attr)=getimagesize('edges/textures/'.Etagere::$texture1.'/'.Etagere::$sous_texture1.'.jpg');
        if ($width<Etagere::$largeur) {
            Etagere::$largeur=$width;
        }
        else {
            Etagere::$largeur=$_POST['largeur'];
        }
        Etagere::$hauteur = $_POST['hauteur'];
        Etagere::$epaisseur = 20;

        list($html, $pourcentage_visible, $liste_magazines) = Edge::getPourcentageVisible($id_user, true);

        $contenu = Edge::getEtagereHTML().$html.Edge::getEtagereHTML(false);

        ob_start();?>

        <div id="largeur_etagere" style="display:none" name="<?=Etagere::$largeur?>"></div>
        <div id="nb_numeros_visibles" style="display:none" name="<?=$pourcentage_visible?>"></div>
        <div id="hauteur_etage" style="display:none" name="<?=Etagere::$hauteur_max_etage?>"></div>
        <div id="grossissement" style="display:none" name="<?=Edge::$grossissement?>"></div>

        <?php
        $contenu.= ob_get_clean();

        $titre = $user === '-1' ? BIBLIOTHEQUE_COURT : (BIBLIOTHEQUE_DE . $user);

        echo json_encode([
            'titre' => $titre,
            'contenu' => $contenu,
            'textures' => $textures,
            'noms_magazines' => $liste_magazines
        ]);
    }
}
elseif (isset($_POST['get_texture'])) {
	$id_user=$_SESSION['id_user'];
	$requete_texture='SELECT Bibliotheque_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select($requete_texture);
	$rep = "edges/textures";
	$dir = opendir($rep);
	while ($f = readdir($dir)) {
		if( $f!=='.' && $f!=='..') {
			?>
			<option 
			<?php
			if ($f==$resultat_texture[0]['Bibliotheque_Texture'.$_POST['n']]) {
                echo 'selected="selected" ';
            } ?>
			value="<?=$f?>"
			><?=constant('TEXTURE__'.strtoupper($f))?></option>
			<?php
		}
	}
}

elseif (isset($_POST['get_sous_texture'])) {
	$id_user=$_SESSION['id_user'];
	$requete_texture='SELECT Bibliotheque_Sous_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select($requete_texture);

	$rep = 'edges/textures/'.$_POST['texture'].'/miniatures';
	$dir = opendir($rep);
	while ($f = readdir($dir)) {
		if( $f!=='.' && $f!=='..') {
			$nom_sous_texture=substr($f,0, strrpos($f, '.'));
			?>
			<option <?php
			if ($nom_sous_texture==$resultat_texture[0]['Bibliotheque_Sous_Texture'.$_POST['n']]) {
                echo 'selected="selected" ';
            } ?>
			style="background:url('edges/textures/<?=$_POST['texture']?>/miniatures/<?=$f?>') no-repeat scroll center right transparent">
				<?=$nom_sous_texture?>
			</option><?php
		}
	}
}
elseif (isset($_POST['partager_bibliotheque'])) {
    Affichage::partager_page();
}

function getEstVisible($pays,$magazine,$numero) {
	$requete_est_visible='SELECT issuenumber FROM tranches_pretes WHERE publicationcode = \''.($pays.'/'.$magazine).'\' AND issuenumber = \''.$numero.'\'';
	return count(DM_Core::$d->requete_select($requete_est_visible)) > 0;
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
		if (!imageistruecolor($img)) {
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
		if (strlen($tmp) < 2) {
            $hex .= "0" . $tmp;
        }
		else {
            $hex .= $tmp;
        }
	}
	return $hex;
}


function remplacerCouleur(&$im,$r_old,$g_old,$b_old,$r,$g,$b) {
	if ($r_old===$r && $g_old===$g && $b_old===$b) {
        return;
    }
	$width = imagesx($im);
	$height = imagesy($im);
	$oldhex = rgb2hex($r_old,$g_old,$b_old);
	$hex = rgb2hex($r,$g,$b);
	$color = imagecolorallocate($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 6)));
	for ($cloneH = 0; $cloneH < $height; $cloneH++) {
		for ($x = 0; $x < $width; $x++) {
			if (colormatch($im, $x, $cloneH, $oldhex)) {
                imagesetpixel($im, $x, $cloneH, $color);
            }
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
