<?php
error_reporting(E_ALL);
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once('locales/lang.php');
include_once('Util.class.php');
include_once('Database.class.php');
class Inducks {
	static $noms_complets;
		static $use_local_db=false;

		static function requete_select($requete,$db='coa',$serveur='serveur_virtuel') {
			if ($serveur=='serveur_virtuel' && Inducks::$use_local_db) {
				mysql_select_db('coa');
				$resultat = DM_Core::$d->requete_select($requete);
				mysql_select_db(DatabasePriv::$nom_db_DM);
				return $resultat;
			}
			else {
				$ip_serveur=$serveur=='serveur_virtuel' ? DatabasePriv::$url_serveur_virtuel : 'http://www.ducksmanager.net';
				$output=unserialize(Util::get_page($ip_serveur.'/sql.php?db='.$db.'&req='.urlencode($requete).'&mdp='.sha1(DatabasePriv::getProfil($serveur)->password)));
				if ($output == '') // Cas des requetes hors SELECT
					return array();
				list($champs,$resultats)=$output;
				foreach($champs as $i_champ=>$nom_champ) {
					foreach($resultats as $i=>$resultat) {
						$resultats[$i][$nom_champ]=$resultat[$i_champ];
					}
				}
			}
			return $resultats;
		}

	static function get_auteur($nom_auteur_abrege) {
			$requete='SELECT fullname FROM inducks_person WHERE personcode = \''.$nom_auteur_abrege.'\'';
			$resultat_requete = Inducks::requete_select($requete);
			return $resultat_requete[0]['fullname']; 
	}

	static function get_vrai_magazine($pays,$magazine) {
		$requete_get_redirection='SELECT NomAbrege FROM magazines WHERE PaysAbrege = \''.$pays.'\' AND RedirigeDepuis = \''.$magazine.'\'';
		$resultat_get_redirection=DM_Core::$d->requete_select($requete_get_redirection);
		if (count($resultat_get_redirection) > 0)
			return $resultat_get_redirection[0]['NomAbrege'];
		return $magazine;
	}
	
	static function get_vrais_magazine_numero($pays,$magazine,$numero) {
		$vrai_magazine=Inducks::get_vrai_magazine($pays,$magazine);
		if ($vrai_magazine !=$magazine) {
			$numero=substr($magazine, strlen($vrai_magazine)).$numero;
			$magazine=$vrai_magazine;
		}
		return array($magazine,$numero);
	}
	
	static function get_numeros_liste_publications($publication_codes) {
		foreach($publication_codes as $i=>$publication_code) {
			$publication_codes[$i]="'".$publication_code."'";
		}
		$requete='SELECT issuenumber, publicationcode FROM inducks_issue '
				.'WHERE publicationcode IN ('.implode(',',$publication_codes).') '
				.'ORDER BY publicationcode';
		$resultat_requete=Inducks::requete_select($requete);
		$resultat_requete=array_map('nettoyer_numero_base_sans_espace',$resultat_requete);
		
		$resultat_final=array();
		foreach($resultat_requete as $resultat) {
			if (!array_key_exists($resultat['publicationcode'],$resultat_final))
				$resultat_final[$resultat['publicationcode']]=array();
			$resultat_final[$resultat['publicationcode']][]=$resultat['issuenumber'];
		}
		return $resultat_final;
	}
		
	static function get_numeros($pays,$magazine,$mode="titres",$sans_espace=false) {
		$magazine_depart=$magazine;
		$magazine=Inducks::get_vrai_magazine($pays,$magazine);
		$fonction_nettoyage=$sans_espace ? 'nettoyer_numero_sans_espace' : 'nettoyer_numero';
		$nom_db_non_coa = DatabasePriv::$nom_db_DM;
		$numeros=array();
		switch($mode) {
			case "urls":
				$urls=array();
				$requete='SELECT issuenumber FROM inducks_issue WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				foreach($resultat_requete as $i=>$numero) {
					$numeros[$i]=call_user_func($fonction_nettoyage, $numero['issuenumber']);
					$urls[$i]='issue.php?c='.$pays.'%2F'.$magazine_depart.str_replace(' ','+',$numero['issuenumber']);
				}
				return array($numeros,$urls);
			break;
			case "titres":
				$titres=array();
				$requete='SELECT issuenumber, title FROM inducks_issue WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				foreach($resultat_requete as $i=>$numero) {
					$numeros[$i]=call_user_func($fonction_nettoyage, $numero['issuenumber']);
					$titres[$i]=$numero['title'];
				}
				return array($numeros,$titres);
			break;
			case "numeros_et_createurs_tranche":
				$requete=' SELECT i.issuenumber, tp2.username contributeurs, IFNULL(tp2.Active, 0) en_cours'
						.' FROM inducks_issue i'
						.' LEFT JOIN '.$nom_db_non_coa.'.tranches_en_cours_modeles tp2 ON CONCAT(tp2.Pays,"/", tp2.Magazine) = i.issuenumber AND tp2.Numero = i.issuenumber AND tp2.Active = 0'
						.' WHERE i.publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				foreach($resultat_requete as $numero) {
					$element_numero = array();
					foreach(array('issuenumber', 'contributeurs', 'en_cours') as $champ) {
						$element_numero[$champ] = $numero[$champ];
					}
					$resultats[]=$element_numero;
				}
				return $resultats;
			break;
			case "numeros_seulement":
				$requete='SELECT issuenumber FROM inducks_issue WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				return array_map('nettoyer_numero_base_sans_espace',$resultat_requete);
				$resultats=array();
				foreach($resultat_requete as $resultat)
					$resultats[]=$resultat['issuenumber'];
				return $resultats;
			break;
		}
	}
	
	static function get_covers($pays,$magazine) {
		$liste=array();
		$page=Util::get_page('http://coa.inducks.org/publication.php?pg=img&c='.$pays.'/'.$magazine);
		$regex_couverture='#<img border=0 src="([^"]+)"></a><br>\(?<a href=issue\.php[^>]+>(?:<span[^>]+>)?([^<]+)</a>\)?#is';
		preg_match_all($regex_couverture,$page,$couvertures);
		foreach($couvertures[0] as $i=>$couverture) {
			$liste[$couvertures[2][$i]]=$couvertures[1][$i];
		}
		return $liste;
	}

	static function get_pays() {
		$requete='SELECT countrycode, countryname FROM inducks_countryname WHERE languagecode = \''.$_SESSION['lang'].'\' ORDER BY countryname';
		$resultat_requete=Inducks::requete_select($requete);
		$liste_pays_courte=array();
		foreach($resultat_requete as $pays) {
			$liste_pays_courte[$pays['countrycode']]=$pays['countryname'];
		}
		return $liste_pays_courte;
	}

	static function get_nom_complet_magazine($pays,$magazine) {
		$requete_pays='SELECT countryname FROM inducks_countryname WHERE languagecode = \''.$_SESSION['lang'].'\' AND countrycode = \''.$pays.'\' ORDER BY countryname';
		$resultat_requete=Inducks::requete_select($requete_pays);
		$nom_pays=$resultat_requete[0]['countryname'];
			
		$requete_magazine='SELECT title FROM inducks_publication WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
		$resultat_requete=Inducks::requete_select($requete_magazine);
		$nom_magazine=$resultat_requete[0]['title'];
			
		return array($nom_pays,$nom_magazine);
	}
	
	static function get_noms_complets($publication_codes) {
		$liste_pays=array();
		$liste_magazines=array();
		foreach($publication_codes as $i=>$publication_code) {
			list($pays,$magazine)=explode('/',$publication_code);
			if (!in_array($pays,$liste_pays))
				$liste_pays["'".$pays."'"]='';
			$publication_codes[$i]="'".$publication_code."'";
		}
		$requete_noms_pays='SELECT countrycode, countryname FROM inducks_countryname '
						  .'WHERE languagecode=\''.$_SESSION['lang'].'\' '
						    .'AND countrycode IN ('.implode(',',array_keys($liste_pays)).')';
		$resultats_noms_pays=Inducks::requete_select($requete_noms_pays);
		$liste_pays=array();
		foreach($resultats_noms_pays as $resultat) {
			$liste_pays[$resultat['countrycode']]=$resultat['countryname'];
		}
		
		$requete_noms_magazines='SELECT publicationcode, title FROM inducks_publication '
							   .'WHERE publicationcode IN ('.implode(',',$publication_codes).')';
		$resultats_noms_magazines=Inducks::requete_select($requete_noms_magazines);
		foreach($resultats_noms_magazines as $resultat) {
			$liste_magazines[$resultat['publicationcode']]=$resultat['title'];
		}
		return array($liste_pays,$liste_magazines);
	}

	static function get_liste_magazines($pays) {
		$requete='SELECT publicationcode, title FROM inducks_publication WHERE countrycode = \''.$pays.'\'';
		$resultat_requete=Inducks::requete_select($requete);
		$liste_magazines_courte=array();
		foreach($resultat_requete as $magazine) {
			list($nom_pays,$nom_magazine_abrege)=explode('/',$magazine['publicationcode']);
			$liste_magazines_courte[$nom_magazine_abrege]=$magazine['title'];
		}
		asort($liste_magazines_courte);
		return $liste_magazines_courte;
	}

	static function get_magazines($pays) {
		$liste=Inducks::get_liste_magazines($pays);
		foreach($liste as $id=>$magazine) {
			echo '<option id="'.$id.'">'.$magazine;
		}
	}

	static function liste_numeros_valide($texte) {
		if (isset($_GET['lang'])) {
			$_SESSION['lang']=$_GET['lang'];
		}
		include_once ('locales/lang.php');
		$regex_retrieve_numeros='#country\^entrycode\^collectiontype\^comment#is';
		return preg_match($regex_retrieve_numeros,$texte,$liste)>0;
	}
	
	static function get_nb_numeros_magazines_pays($pays) {
		$requete='SELECT publicationcode, Count(issuenumber) AS cpt FROM inducks_issue WHERE publicationcode LIKE \''.$pays.'/%\' GROUP BY publicationcode';
		$resultat_requete=Inducks::requete_select($requete);
		$nb_numeros=array();
		foreach($resultat_requete as $magazine) {
			list($nom_pays,$nom_magazine_abrege)=explode('/',$magazine['publicationcode']);
			$nb_numeros[$nom_magazine_abrege]=$magazine['cpt'];
		}
		return $nb_numeros;
	}
	static function numero_to_page($pays,$magazine,$numero) {
		$magazine=strtoupper($magazine);
		list($urls,$numeros)=Inducks::get_numeros($pays, $magazine,"urls",true);
		if (false!==($i=array_search($numero, $numeros)))
			return Util::get_page('http://coa.inducks.org/'.$urls[$i]);
		else
			return ERREUR_CONNEXION_INDUCKS;
	}
}
if (isset($_POST['get_pays'])) {
	$liste_pays_courte=Inducks::get_pays();

	foreach($liste_pays_courte as $id=>$pays) {
		if ($pays=='France')
			echo '<option selected="selected" id="'.$id.'">'.$pays;
		else
			echo '<option id="'.$id.'">'.$pays;
	}
}
elseif (isset($_POST['get_magazines'])) {
	Inducks::get_magazines($_POST['pays']);
}
elseif (isset($_POST['get_numeros'])) {
	Inducks::get_numeros($_POST['pays'],$_POST['magazine']);
}
elseif (isset($_POST['get_cover'])) {
	$resultats=array();
	$regex_num_alternatif='#([A-Z]+)([0-9]+)#';
	$numero_alternatif=preg_match($regex_num_alternatif, $_POST['numero']) == 0 ? null : preg_replace($regex_num_alternatif, '$1[ ]*$2', $_POST['numero']);
	$liste_magazines=array();
	$_POST['numero']=str_replace(' ','',$_POST['numero']);
	$_POST['magazine']=strtoupper($_POST['magazine']);
	$requete_get_extraits='SELECT sitecode, position, url FROM inducks_issue '
						 .'INNER JOIN inducks_entry ON inducks_issue.issuecode = inducks_entry.issuecode '
						 .'INNER JOIN inducks_entryurl ON inducks_entry.entrycode = inducks_entryurl.entrycode '
						 .'WHERE inducks_issue.publicationcode = \''.$_POST['pays'].'/'.$_POST['magazine'].'\' ' 
						 .'AND (REPLACE(issuenumber,\' \',\'\') = \''.$_POST['numero'].'\' '.(is_null($numero_alternatif) ? '':'OR REPLACE(issuenumber,\' \',\'\') REGEXP \''.$numero_alternatif.'\'').') '
						 //.'AND inducks_entryurl.sitecode = \'webusers\' '
						 .'GROUP BY inducks_entry.entrycode '
						 .'ORDER BY position';
	$resultat_get_extraits=Inducks::requete_select($requete_get_extraits);
	$i=0;
	foreach($resultat_get_extraits as $extrait) {
		switch($extrait['sitecode']) {
			case 'webusers': case 'thumbnails':
				$url='http://outducks.org/webusers/'.$extrait['url'];
			break;
			default:
				$url='http://outducks.org/'.$extrait['sitecode'].'/'.$extrait['url'];
		}
		
		if (count($resultats) == 0)
			$resultats['cover']=$url;
		else {	
			$num_page=$extrait['position'];
			if (preg_match('#p.+#i', $num_page) == 0)
				$num_page=-99+($i++);
			else
				$num_page=substr ($num_page, 1);
			$resultats[]=array('page'=>$num_page,'url'=>$url);
		}
	}
	if (count($resultat_get_extraits) == 0)
		$resultats['cover']='images/cover_not_found.png';
	
	echo header("X-JSON: " . json_encode($resultats));
}
elseif (isset($_POST['get_covers'])) {
	echo header("X-JSON: " . json_encode(Inducks::get_covers($_POST['pays'], $_POST['magazine'])));
}
elseif (isset($_POST['get_magazines_histoire'])) {
	$nom_histoire=Util::supprimerAccents(utf8_decode($_POST['histoire']));
	echo $nom_histoire."\n";
	$liste_magazines=array();
	if (strpos($nom_histoire, 'code=') === 0) {
		$liste_magazines['direct']=true;
		$code=substr($nom_histoire, strlen('code='));
		$requete='SELECT inducks_issue.publicationcode AS publicationcode, inducks_issue.issuenumber AS issuenumber '
				.'FROM inducks_issue '
				.'INNER JOIN inducks_entry ON inducks_issue.issuecode = inducks_entry.issuecode '
				.'INNER JOIN inducks_storyversion ON inducks_entry.storyversioncode = inducks_storyversion.storyversioncode '
				.'WHERE storycode = \''.$code.'\' '
				.'ORDER BY publicationcode, issuenumber';
		$resultat_requete=Inducks::requete_select($requete);
		$publication_codes=array();
		foreach($resultat_requete as $resultat) {
			$publication_codes[]=$resultat['publicationcode'];
		}
		list($noms_pays,$noms_magazines) = Inducks::get_noms_complets($publication_codes);
		foreach($resultat_requete as $resultat) {
			list($pays,$magazine)=explode('/',$resultat['publicationcode']);
			$nom_complet_magazine=$noms_magazines[$resultat['publicationcode']];
			$issuenumber=$resultat['issuenumber'];
			$liste_magazines[]=array('pays'=>$pays,
									 'magazine_numero'=>$magazine.'.'.$issuenumber,
									 'nom_magazine'=>$nom_complet_magazine,
									 'titre'=>$nom_complet_magazine);
		}
	}
	else {
		$requete='SELECT DISTINCT inducks_storyversion.storycode AS storycode, inducks_entry.title AS title '
				.'FROM inducks_entry '
				.'INNER JOIN inducks_storyversion ON inducks_entry.storyversioncode = inducks_storyversion.storyversioncode '
				.'WHERE inducks_entry.title LIKE \'%'.$nom_histoire.'%\' '
				.'ORDER BY title';
		$resultat_requete=Inducks::requete_select($requete);
		foreach($resultat_requete as $resultat) {
			$code=$resultat['storycode'];
			$title=$resultat['title'];
			$liste_magazines[]=array('code'=>$code,
									 'titre'=>$title);
		}
		if (count($liste_magazines) > 10) {
			$liste_magazines=array_slice($liste_magazines, 0,10);
			$liste_magazines['limite']=true;
		}
	}
	$requete='SELECT publicationcode, Count(issuenumber) AS cpt FROM inducks_issue WHERE publicationcode LIKE \''.$pays.'/%\' GROUP BY publicationcode';
	$resultat_requete=Inducks::requete_select($requete);
	
	echo header("X-JSON: " . json_encode($liste_magazines));
}

function trier_resultats_recherche ($a,$b) {
	if ($a['titre'] < $b['titre'])
		return -1;
	else
		return $a['titre'] == $b['titre'] ? 0 : 1;
}
		
function nettoyer_numero($numero) {
	return str_replace("\n",'',preg_replace('#[+ ]+#is',' ',$numero));
}
		
function nettoyer_numero_sans_espace($numero) {
	return str_replace("\n",'',preg_replace('#[+ ]+#is','',$numero));
}

function nettoyer_numero_base_sans_espace($ligne_resultat) {
	$ligne_resultat['issuenumber'] = nettoyer_numero_sans_espace($ligne_resultat['issuenumber']);
	return $ligne_resultat;
}
?>
