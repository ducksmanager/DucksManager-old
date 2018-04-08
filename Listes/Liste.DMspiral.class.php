<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
require_once 'Format_liste.php';
class dmspiral extends Format_liste {
	static $titre='Liste en spirale';
	
	function __construct() {
		$this->description='';//DMSPIRAL_DESCRIPTION;
		$this->ajouter_parametres([
			'epaisseur'=>new Parametre_min_max('Epaisseur des cases',10,25,30,15),
			'marge'=>new Parametre_min_max('Marge',0,5,2,2),
			'hauteur_centrale'=>new Parametre_min_max('Hauteur centrale',25,60,40,40),
			'nuance_gris_fond'=>new Parametre_min_max('Nuance de gris du fond',100,255,255,255),
			'couleur_r'=>new Parametre_min_max('Remplissage - rouge',0,255,0,0),
			'couleur_g'=>new Parametre_min_max('Remplissage - vert',0,255,0,0),
			'couleur_b'=>new Parametre_min_max('Remplissage - bleu',0,255,255,255)]);
		
		$this->ajouter_parametres([
			'taille_police'=>new Parametre_fixe($this->p('epaisseur')/4)]);
	}
	
	static function est_listable($numero) {
		return is_numeric($numero) || preg_match(Format_liste::$regex_numero_double, $numero, $numero)>0;
	}
	
	function afficher($liste) {

		foreach($liste as $pays=>$numeros_pays) {
			foreach(array_keys($numeros_pays) as $magazine) {
				?><img alt="dmspiral" src="Listes/Liste.dmspiral.class.php?pays=<?=$pays?>&magazine=<?=$magazine?>&amp;parametres=<?=str_replace("\'","'",str_replace('"','|',json_encode($this->parametres)))?>" /><?php
			}
		}
	}
	
	function generer($pays,$magazine) {
		if (!isset($_GET['debug'])) {
            header('Content-type: image/png');
        }
	
		$numeros_doubles= [];
		list($numeros,)=Inducks::get_numeros($pays,$magazine);
		$this->ajouter_parametres(['numero_max'=>max($numeros)]);
		$this->ajouter_parametres(['nb_centaines'=> (int)$this->p('numero_max') / 100 +1]);
		$this->ajouter_parametres([
			'haut'=>$this->p('marge')+$this->p('nb_centaines')*$this->p('epaisseur')/2,
			'gauche'=>$this->p('marge')+$this->p('nb_centaines')*$this->p('epaisseur')/4]);
		foreach($numeros as $numero) {
			$est_numero_double=preg_match(self::$regex_numero_double, $numero, $numero)>0;
			if ($est_numero_double) {
				$premier_numero = $numero[1] . $numero[2];
				$numeros_doubles[]=$premier_numero;
			}
		}

		$nom_magazine_complet=Inducks::get_nom_complet_magazine($pays, $magazine);

		$titre=mb_strtoupper($nom_magazine_complet,'UTF-8');
		
		$image=imagecreatetruecolor(100+(48+$this->p('nb_centaines')/2)*$this->p('epaisseur')-10+$this->p('marge')+10, $this->p('epaisseur')*$this->p('nb_centaines')+$this->p('hauteur_centrale')+2+$this->p('marge')*2);
        if (function_exists('imageantialias')) {
		    imageantialias($image, true);
        }
		imagecolorallocate($image, 255,255,255);
		$gris_clair=imagecolorallocate($image, $this->p('nuance_gris_fond'),$this->p('nuance_gris_fond'),$this->p('nuance_gris_fond'));
		imagefill($image, 0,0, $gris_clair);

		$noir=  imagecolorallocate($image, 0,0,0);
		imagecolorallocate($image, 255,255,255);

		/** CREATION DE LA GRILLE **/

		imagettftext($image, $this->p('taille_police')*0.55, 0, $this->p('gauche')+$this->p('epaisseur')/4, $this->p('haut')+$this->p('epaisseur')*0.75, $noir, 'arial.ttf', 1);
		imagettftext($image, $this->p('taille_police')*0.55, 0, $this->p('gauche')+47.3*$this->p('epaisseur'), $this->p('haut')+$this->p('epaisseur')*0.75, $noir, 'arial.ttf', 49);
		imagettftext($image, $this->p('taille_police')*0.8, 0, $this->p('gauche')+46.8*$this->p('epaisseur'), $this->p('taille_police')*0.8/2+$this->p('haut')+$this->p('hauteur_centrale')/2, $noir, 'arial.ttf', 50);
		imagettftext($image, $this->p('taille_police')*0.55, 0, $this->p('gauche')+47.3*$this->p('epaisseur'), $this->p('haut')+2+$this->p('hauteur_centrale')-$this->p('epaisseur')/2, $noir, 'arial.ttf', 51);
		imagettftext($image, $this->p('taille_police')*0.8, 0, $this->p('gauche')+$this->p('epaisseur')*0.5, $this->p('taille_police')*0.8/2+$this->p('haut')+$this->p('hauteur_centrale')/2, $noir, 'arial.ttf', 100);
		imagettftext($image, $this->p('taille_police')*0.55, 0, $this->p('gauche')+$this->p('epaisseur')/4, $this->p('haut')+2+$this->p('hauteur_centrale')-$this->p('epaisseur')/2, $noir, 'arial.ttf', 99);

		imagearc($image, $this->p('gauche')+$this->p('epaisseur')/2, $this->p('haut')-1+$this->p('epaisseur')/2, $this->p('epaisseur')/2, 2+$this->p('epaisseur'), 180, 270, $noir);
		imageline($image, $this->p('gauche'), $this->p('haut')-1+$this->p('epaisseur')/2, $this->p('gauche')+0.3*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/2, $noir);
		imageline($image,$this->p('gauche')+$this->p('epaisseur')/2,$this->p('haut')-1,$this->p('gauche')+47.5*$this->p('epaisseur'),$this->p('haut')-1,$noir);
		for ($i=2;$i<=48;$i++) {
			$numero=$i;
			imagettftext($image, $this->p('taille_police'), 0, $this->p('gauche')+($i-1.3)*$this->p('epaisseur'), $this->p('haut')+1+$this->p('epaisseur')*0.5, $noir, 'arial.ttf', $numero);
			$numero=100-$i;
			imagettftext($image, $this->p('taille_police'), 0, $this->p('gauche')+($i-1.3)*$this->p('epaisseur'), $this->p('haut')-2+$this->p('hauteur_centrale'), $noir, 'arial.ttf', $numero);
		}
		imagettftext($image, $this->p('taille_police'), 0, $this->p('gauche')+23.5*$this->p('epaisseur'), $this->p('taille_police')/2+$this->p('haut')+$this->p('hauteur_centrale')/2, $noir, 'arial.ttf', $titre);

		imagearc($image, $this->p('gauche')+47.5*$this->p('epaisseur'), $this->p('haut')-2+$this->p('epaisseur')/2, $this->p('epaisseur')/2, $this->p('epaisseur'), 270, 360, $noir);
		imagearc($image, $this->p('gauche')+47.5*$this->p('epaisseur'), $this->p('haut')-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $this->p('epaisseur')/2, $this->p('epaisseur'), 0, 90, $noir);

		imageline($image,$this->p('gauche')+47.5*$this->p('epaisseur'),$this->p('haut')-1+$this->p('hauteur_centrale'),$this->p('gauche')+$this->p('epaisseur')/2,$this->p('haut')-1+$this->p('hauteur_centrale'),$noir);

		imageline($image, $this->p('gauche')+47.75*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/2, $this->p('gauche')+47.75*$this->p('epaisseur'), $this->p('haut')+1-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $noir);

		imagearc($image, $this->p('gauche')+$this->p('epaisseur')/2, $this->p('haut')-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $this->p('epaisseur'), $this->p('epaisseur'), 90, 180, $noir);
		imageline($image, $this->p('gauche'), $this->p('haut')-1+$this->p('epaisseur')/2, $this->p('gauche'), $this->p('haut')+1-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $noir);

		for ($centaine=0;$centaine<$this->p('nb_centaines');$centaine++) {

			imagearc($image, $this->p('gauche')+$this->p('epaisseur')/2, $this->p('haut')-.5, $this->p('epaisseur')+$centaine*$this->p('epaisseur')/2, $this->p('epaisseur')*($centaine+1), 180, 270, $noir);	
			imageline($image, $this->p('gauche')-$centaine*$this->p('epaisseur')/4, $this->p('haut')-1, $this->p('gauche')-$centaine*$this->p('epaisseur')/4, $this->p('haut')+$this->p('epaisseur')/2, $noir);

			imageline($image,$this->p('gauche')+$this->p('epaisseur')/2,$this->p('haut')-($centaine+1)*$this->p('epaisseur')/2,$this->p('gauche')+47.5*$this->p('epaisseur'),$this->p('haut')-($centaine+1)*$this->p('epaisseur')/2,$noir);

			for ($i=1;$i<49;$i++) {
				imageline($image, $this->p('gauche')+($i-0.5)*$this->p('epaisseur'),$this->p('haut')-($centaine+1)*$this->p('epaisseur')/2, $this->p('gauche')+($i-0.5)*$this->p('epaisseur'),$this->p('haut')-$centaine*$this->p('epaisseur')/2, $noir);
				imageline($image, $this->p('gauche')+($i-0.5)*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+1)*$this->p('epaisseur')/2, $this->p('gauche')+($i-0.5)*$this->p('epaisseur'),$this->p('haut')-1+$this->p('hauteur_centrale')+$centaine*$this->p('epaisseur')/2, $noir);
			}

			imageline($image,$this->p('gauche')+$this->p('epaisseur')/2,$this->p('haut')+$this->p('hauteur_centrale')+($centaine+1)*$this->p('epaisseur')/2,$this->p('gauche')+47.5*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+1)*$this->p('epaisseur')/2,$noir);

			imagearc($image, $this->p('gauche')+47.5*$this->p('epaisseur'), $this->p('haut'), $this->p('epaisseur')+$centaine*$this->p('epaisseur')/2, $this->p('epaisseur')*($centaine+1), 270, 360, $noir);
			imagearc($image, $this->p('gauche')+47.5*$this->p('epaisseur'), $this->p('haut')+$this->p('hauteur_centrale'), $this->p('epaisseur')+$centaine*$this->p('epaisseur')/2, $this->p('epaisseur')*($centaine+1), 0, 90, $noir);

			imageline($image, $this->p('gauche')+(47.75+($centaine+1)/4)*$this->p('epaisseur'), $this->p('haut')-1, $this->p('gauche')+(47.75+($centaine+1)/4)*$this->p('epaisseur'), $this->p('haut')+1+$this->p('hauteur_centrale'), $noir);
			imageline($image, $this->p('gauche')+(47.75+$centaine/4)*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/2, $this->p('gauche')+(47.75+($centaine+1)/4)*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/2, $noir);
			imageline($image, $this->p('gauche')+(47.75+$centaine/4)*$this->p('epaisseur'), $this->p('haut')+1-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $this->p('gauche')+(47.75+($centaine+1)/4)*$this->p('epaisseur'), $this->p('haut')+1-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $noir);

			imagearc($image, $this->p('gauche')+$this->p('epaisseur')/2, $this->p('haut')+$this->p('hauteur_centrale'), $this->p('epaisseur')+($centaine+1)*$this->p('epaisseur')/2, $this->p('epaisseur')*($centaine+1), 90, 180, $noir);

			imageline($image, $this->p('gauche')-(($centaine+1)*0.25)*$this->p('epaisseur'), $this->p('haut')+$this->p('epaisseur')/2, $this->p('gauche')-(($centaine+1)*0.25)*$this->p('epaisseur'), $this->p('haut')+1+$this->p('hauteur_centrale'), $noir);
			imageline($image, $this->p('gauche')-(($centaine+1)*0.25)*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/2, $this->p('gauche')-($centaine*0.25)*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/2, $noir);
			imageline($image, $this->p('gauche')-(($centaine+1)*0.25)*$this->p('epaisseur'), $this->p('haut')+1-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $this->p('gauche')-($centaine*0.25)*$this->p('epaisseur'), $this->p('haut')+1-$this->p('epaisseur')/2+$this->p('hauteur_centrale'), $noir);
		}
		foreach($numeros_doubles as $numero_double) {
			$centaine= (int)$numero_double / 100;
			$diz_unites=$numero_double-100*$centaine;
			switch($diz_unites) {
				case 0:
					imagefilledrectangle($image, $this->p('gauche')+0.3*$this->p('epaisseur')/4-($centaine+1)*$this->p('epaisseur')/4, $this->p('haut')-1+$this->p('epaisseur')/4, 
												 $this->p('gauche')+0.7*$this->p('epaisseur')/4-($centaine+1)*$this->p('epaisseur')/4, $this->p('haut')-1+$this->p('epaisseur')/4+$this->p('hauteur_centrale')/2,
												 $gris_clair);

				break;
				case 1:
					imagefilledrectangle($image, $this->p('gauche')+.5*$this->p('epaisseur'),$this->p('haut')-($centaine+0.75)*$this->p('epaisseur')/2, 
												 $this->p('gauche')+$this->p('epaisseur'),$this->p('haut')-($centaine+0.25)*$this->p('epaisseur')/2,
												 $gris_clair);

				break;
				case 48:
					imagefilledrectangle($image, $this->p('gauche')+($diz_unites-1)*$this->p('epaisseur'),$this->p('haut')-($centaine+0.75)*$this->p('epaisseur')/2, 
												 $this->p('gauche')+($diz_unites-.5)*$this->p('epaisseur'),$this->p('haut')-($centaine+0.25)*$this->p('epaisseur')/2,
												 $gris_clair);
				break;
				case 49:
					imagefilledrectangle($image, $this->p('gauche')+(47.8+$centaine/4)*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/4, 
												 $this->p('gauche')+(47.95+ $centaine /4)*$this->p('epaisseur'), $this->p('haut')-1+$this->p('epaisseur')/4+$this->p('hauteur_centrale')/2,
												 $gris_clair);

				break;
				case 50:
					imagefilledrectangle($image, $this->p('gauche')+(47.8+$centaine/4)*$this->p('epaisseur'), $this->p('haut')+1+$this->p('hauteur_centrale')-$this->p('epaisseur'), 
												 $this->p('gauche')+(47.95+ $centaine /4)*$this->p('epaisseur'), $this->p('haut')+1+$this->p('hauteur_centrale')-$this->p('epaisseur')/2+$this->p('epaisseur')/4,
												 $gris_clair);

				break;
				case 51:
					imagefilledrectangle($image, $this->p('gauche')+(99-$diz_unites-1)*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+0.75)*$this->p('epaisseur')/2, 
												 $this->p('gauche')+(99-$diz_unites-.5)*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+0.25)*$this->p('epaisseur')/2,
												 $gris_clair);
				break;
				case 98:
					imagefilledrectangle($image, $this->p('gauche')+(99-$diz_unites-0.5)*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+0.75)*$this->p('epaisseur')/2, 
												 $this->p('gauche')+(99-$diz_unites)*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+0.25)*$this->p('epaisseur')/2,
												 $gris_clair);
				break;
				case 99:
					imagefilledrectangle($image, $this->p('gauche')+0.3*$this->p('epaisseur')/4-($centaine+1)*$this->p('epaisseur')/4,$this->p('haut')+1+$this->p('hauteur_centrale')-$this->p('epaisseur'), 
												 $this->p('gauche')+0.7*$this->p('epaisseur')/4-($centaine+1)*$this->p('epaisseur')/4,$this->p('haut')+1+$this->p('hauteur_centrale')-$this->p('epaisseur')/2+$this->p('epaisseur')/4,
												 $gris_clair);

				break;
				default:
					if ($diz_unites<49) {
						imagefilledrectangle($image, $this->p('gauche')+($diz_unites-1)*$this->p('epaisseur'),$this->p('haut')-($centaine+0.75)*$this->p('epaisseur')/2, 
													 $this->p('gauche')+ $diz_unites *$this->p('epaisseur'),$this->p('haut')-($centaine+0.25)*$this->p('epaisseur')/2,
													 $gris_clair);
					}
					else {
						$diz_unites=99-$diz_unites;
						imagefilledrectangle($image, $this->p('gauche')+($diz_unites-1)*$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+0.75)*$this->p('epaisseur')/2, 
													 $this->p('gauche')+ $diz_unites *$this->p('epaisseur'),$this->p('haut')+$this->p('hauteur_centrale')+($centaine+0.25)*$this->p('epaisseur')/2,
													 $gris_clair);

					}
			}
		}

		for ($i=0;$i<$this->p('nb_centaines');$i++) {
			imagettftext($image, $this->p('taille_police')*0.55, 0, $this->p('gauche')+$this->p('nb_centaines')*$this->p('epaisseur')/4+48*$this->p('epaisseur'), $this->p('haut')+$this->p('hauteur_centrale')+($i+1)*$this->p('epaisseur')/2, $noir, 'arial.ttf', (100*$i+1).'..'.(100*($i+1)));
			imagettftext($image, $this->p('taille_police')*0.55, 0, $this->p('gauche')+$this->p('nb_centaines')*$this->p('epaisseur')/4+48*$this->p('epaisseur'), $this->p('haut')- $i *$this->p('epaisseur')/2, $noir, 'arial.ttf', (100*$i+1).'..'.(100*($i+1)));
		}

		$requete_numeros_possedes='SELECT Numero FROM numeros WHERE (Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND ID_Utilisateur='.$_SESSION['id_user'].')';
		$resultat_numeros_possedes=DM_Core::$d->requete_select($requete_numeros_possedes);
		foreach($resultat_numeros_possedes as $numero) {
			if (0!= (int)$numero['Numero']) {
				$est_numero_double=preg_match(self::$regex_numero_double, $numero['Numero'], $numero2)>0;
				if ($est_numero_double) {
					$premier_numero = $numero2[1] . $numero2[2];
					$this->marquer_numero ($image, $premier_numero);
				}
				else {
                    $this->marquer_numero($image, $numero['Numero']);
                }
			}
		}
		/** REMPLISSAGE **/

		imagepng($image);


		/** AFFICHAGE DES CENTAINES **/
		for($i=0;$i<=$this->p('nb_centaines');$i++) {
			$pos=new stdClass();
			$pos->x=$this->p('gauche')+(47.87+$this->p('nb_centaines')/4)*$this->p('epaisseur');
		}
	}
	
	function marquer_numero($image,$numero) {
		$centaine= (int)$numero / 100;
		$diz_unites=$numero-100*$centaine;
		$pos=new stdClass();
		switch($diz_unites) {
			case 0:			
				$pos->x=$this->p('gauche')+(0.12-$centaine/4)*$this->p('epaisseur');
				$pos->y=$this->p('haut')-1+$this->p('hauteur_centrale')-$this->p('epaisseur');
			break;
			case 1:
				$pos->x=$this->p('gauche')+$this->p('epaisseur')*($diz_unites-1)+$this->p('epaisseur')/4;
				$pos->y=$this->p('haut')-($centaine+1)*$this->p('epaisseur')/2+$this->p('epaisseur')/3;
			break;
			case 49:				
				$pos->x=$this->p('gauche')+$this->p('epaisseur')*($diz_unites-1)-$this->p('epaisseur')/4;
				$pos->y=$this->p('haut')-($centaine+1)*$this->p('epaisseur')/2+$this->p('epaisseur')/3;
			break;
			case 50:
				$pos->x=$this->p('gauche')+(47.87+$centaine/4)*$this->p('epaisseur');
				$pos->y=$this->p('haut')-1+$this->p('hauteur_centrale')-$this->p('epaisseur');
			break;
			case 51:
				$pos->x=$this->p('gauche')+$this->p('epaisseur')*(50-($diz_unites-50)-1)-$this->p('epaisseur')/4;
				$pos->y=$this->p('haut')+$this->p('hauteur_centrale')+($centaine+1)*$this->p('epaisseur')/2-$this->p('epaisseur')/3;
			break;
			case 99:
				$pos->x=$this->p('gauche')+$this->p('epaisseur')*(50-($diz_unites-50)-1)+$this->p('epaisseur')/4;
				$pos->y=$this->p('haut')+$this->p('hauteur_centrale')+($centaine+1)*$this->p('epaisseur')/2-$this->p('epaisseur')/3;
			break;

			default:
				if ($diz_unites<50) {
					$pos->x=$this->p('gauche')+$this->p('epaisseur')*($diz_unites-1);
					$pos->y=$this->p('haut')-($centaine+1)*$this->p('epaisseur')/2+$this->p('epaisseur')/4;
				}
				else {
					$pos->x=$this->p('gauche')+$this->p('epaisseur')*(50-($diz_unites-50)-1);
					$pos->y=$this->p('haut')+$this->p('hauteur_centrale')+ $centaine *$this->p('epaisseur')/2+$this->p('epaisseur')/4;
				}
			break;
		}
		$couleur=imagecolorallocate($image, $this->p('couleur_r'), $this->p('couleur_g'), $this->p('couleur_b'));
		imagefill($image, $pos->x, $pos->y, $couleur);
	}
}



if (isset($_GET['pays'], $_GET['magazine'])) {
	include_once '../Inducks.class.php';
	$dmspiral=new dmspiral();
	if (isset($_GET['parametres'])) {
		$parametres=json_decode(str_replace('|','"',$_GET['parametres']));
		foreach($parametres as $nom_parametre=>$parametre) {
            $dmspiral->parametres->$nom_parametre = $parametre;
        }
	}
	$dmspiral->generer($_GET['pays'], $_GET['magazine']);
	
}
?>