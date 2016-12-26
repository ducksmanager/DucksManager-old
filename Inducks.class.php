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
	static $use_local_db=true;

	static function connexion_ok() {
		$requete='SELECT COUNT(*) As cpt FROM inducks_country';
		$resultat=Inducks::requete_select($requete);
		return is_array($resultat) && count($resultat) > 0;
	}

	static function requete_select($requete, $db = 'db_coa') {
		if (count(ServeurCoa::$coa_servers) === 0) {
			ServeurCoa::initCoaServers();
		}

		foreach(ServeurCoa::$coa_servers as $coaServerName=>$coaServer) {
			$output=Util::get_query_results_from_remote($coaServer, $requete, $db);
			if (isset($_GET['brut'])) {
				echo 'Requete : '.$requete.'<br />'
					.'Retour brut : <pre>'.$output.'</pre>'
					.'Stacktrace : <pre>';
				debug_print_backtrace();
				echo '</pre>';
			}
			if (is_null($output)) {
				return [];
			}
			else {
				$results = json_decode($output, true);
				if (is_array($results)) {
					return $results;
				}
				else {
					unset(ServeurCoa::$coa_servers[$coaServerName]);
				}
			}
		}
		return [];
	}

	static function get_auteur($nom_auteur_abrege) {
		$requete='SELECT fullname FROM inducks_person WHERE personcode = \''.$nom_auteur_abrege.'\'';
		$resultat_requete = Inducks::requete_select($requete);
		if (count($resultat_requete) === 0) {
			return null;
		}
		else {
			return $resultat_requete[0]['fullname'];
		}
	}

	static function get_vrai_magazine($pays,$magazine) {
		$requete_get_redirection='SELECT NomAbrege FROM magazines WHERE PaysAbrege = \''.$pays.'\' AND RedirigeDepuis = \''.$magazine.'\'';
		$resultat_get_redirection=DM_Core::$d->requete_select_distante($requete_get_redirection);
		if (count($resultat_get_redirection) > 0)
			return $resultat_get_redirection[0]['NomAbrege'];
		return $magazine;
	}

	static function get_vrais_magazine_et_numero($pays,$magazine,$numero) {
		$vrai_magazine=self::get_vrai_magazine($pays,$magazine);
		if ($vrai_magazine != $magazine) {
			return [$vrai_magazine,substr($magazine, strlen($vrai_magazine)).$numero];
		}
		else {
			return [$magazine,$numero];
		}
	}

	static function get_numeros_liste_publications($publication_codes) {
		$publication_codes = array_map(function($publication_code) {
			return "'".$publication_code."'";
		}, $publication_codes);

		$numeros = [];

		$max_publication_codes_request = 20;
		for ($offset = 0; $offset < count($publication_codes); $offset += $max_publication_codes_request) {
			$current_chunk_size = min(count($publication_codes) - $offset, $max_publication_codes_request);
			$publication_codes_chunk = array_slice($publication_codes, $offset, $current_chunk_size);
			$requete='SELECT issuenumber, publicationcode FROM inducks_issue '
					.'WHERE publicationcode IN ('.implode(',',$publication_codes_chunk).') '
					.'ORDER BY publicationcode';
			$resultat_requete=Inducks::requete_select($requete);
			$numeros= array_merge($numeros, $resultat_requete);
		}
		$numeros= array_map('nettoyer_numero_base_sans_espace',$numeros);

		$resultat_final= [];
		foreach($numeros as $numero) {
			if (!array_key_exists($numero['publicationcode'],$resultat_final))
				$resultat_final[$numero['publicationcode']]= [];
			$resultat_final[$numero['publicationcode']][]=$numero['issuenumber'];
		}
		return $resultat_final;
	}

	static function get_numeros($pays,$magazine,$mode="titres",$sans_espace=false) {
		$magazine_depart=$magazine;
		$magazine=Inducks::get_vrai_magazine($pays,$magazine);
		$fonction_nettoyage=$sans_espace ? 'nettoyer_numero_sans_espace' : 'nettoyer_numero';
		$nom_db_non_coa = ServeurDb::$nom_db_DM;
		$numeros= [];
		switch($mode) {
			case "urls":
				$urls= [];
				$requete='SELECT issuenumber FROM inducks_issue WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				foreach($resultat_requete as $i=>$numero) {
					$numeros[$i]=call_user_func($fonction_nettoyage, $numero['issuenumber']);
					$urls[$i]='issue.php?c='.$pays.'%2F'.$magazine_depart.str_replace(' ','+',$numero['issuenumber']);
				}
				return [$numeros,$urls];
			break;
			case "titres":
				$titres= [];
				$requete='SELECT issuenumber, title FROM inducks_issue WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				foreach($resultat_requete as $i=>$numero) {
					$numeros[$i]=call_user_func($fonction_nettoyage, $numero['issuenumber']);
					$titres[$i]=$numero['title'];
				}
				return [$numeros,$titres];
			break;
			case "numeros_et_createurs_tranche":
				$requete=' SELECT i.issuenumber, tp2.username contributeurs, IF(tp2.Active=0, 1, 0) en_cours'
						.' FROM inducks_issue i'
						.' LEFT JOIN '.$nom_db_non_coa.'.tranches_en_cours_modeles tp2 ON CONCAT(tp2.Pays,"/", tp2.Magazine) = i.publicationcode AND tp2.Numero = i.issuenumber AND tp2.Active = 0'
						.' WHERE i.publicationcode = \''.$pays.'/'.$magazine.'\'';
				$resultat_requete=Inducks::requete_select($requete);
				$resultats = [];
				foreach($resultat_requete as $numero) {
					$element_numero = [];
					foreach(['issuenumber', 'contributeurs', 'en_cours'] as $champ) {
						$element_numero[$champ] = $numero[$champ];
					}
					$element_numero['issuenumber'] = $fonction_nettoyage($element_numero['issuenumber']);
					$resultats[]=$element_numero;
				}
				return $resultats;
			break;
		}
		return [];
	}

	static function get_pays() {
		$requete='SELECT countrycode, countryname FROM inducks_countryname WHERE languagecode = \''.$_SESSION['lang'].'\' ORDER BY countryname';
		$resultat_requete=Inducks::requete_select($requete);
		$liste_pays_courte= [];
		foreach($resultat_requete as $pays) {
			$liste_pays_courte[$pays['countrycode']]=$pays['countryname'];
		}
		return $liste_pays_courte;
	}

	static function get_nom_complet_magazine($pays,$magazine) {
		$requete_magazine='SELECT title FROM inducks_publication WHERE publicationcode = \''.$pays.'/'.$magazine.'\'';
		$resultat_requete=Inducks::requete_select($requete_magazine);

		return $resultat_requete[0]['title'];
	}

	static function get_noms_complets($publication_codes) {
		$liste_pays_complets= [];
		$liste_magazines_complets= [];

		$publication_codes_chunks=array_chunk($publication_codes, 100);
		foreach($publication_codes_chunks as $publication_codes_chunk) {
			$liste_pays= [];
			foreach($publication_codes_chunk as $i=>$publication_code) {
				list($pays,$magazine)=explode('/',$publication_code);
				$liste_pays=array_merge($liste_pays, ["'".$pays."'"]);
				$publication_codes_chunk[$i]="'".$publication_code."'";
			}
			$requete_noms_pays='SELECT countrycode, countryname FROM inducks_countryname '
							  .'WHERE languagecode=\''.$_SESSION['lang'].'\' '
							    .'AND countrycode IN ('.implode(',',$liste_pays).')';
			$resultats_noms_pays=Inducks::requete_select($requete_noms_pays);
			foreach($resultats_noms_pays as $resultat) {
				$liste_pays_complets[$resultat['countrycode']]=$resultat['countryname'];
			}

			$requete_noms_magazines='SELECT publicationcode, title FROM inducks_publication '
								   .'WHERE publicationcode IN ('.implode(',',$publication_codes_chunk).')';
			$resultats_noms_magazines=Inducks::requete_select($requete_noms_magazines);
			foreach($resultats_noms_magazines as $resultat) {
				$liste_magazines_complets[$resultat['publicationcode']]=$resultat['title'];
			}
		}
		return [$liste_pays_complets,$liste_magazines_complets];
	}

	static function get_liste_magazines($pays) {
		$requete='SELECT publicationcode, title FROM inducks_publication WHERE countrycode = \''.$pays.'\'';
		$resultat_requete=Inducks::requete_select($requete);
		$liste_magazines_courte= [];
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
		return preg_match($regex_retrieve_numeros,$texte)>0;
	}

	static function get_nb_numeros_magazines_pays($pays) {
		$requete='SELECT publicationcode, Count(issuenumber) AS cpt FROM inducks_issue WHERE publicationcode LIKE \''.$pays.'/%\' GROUP BY publicationcode';
		$resultat_requete=Inducks::requete_select($requete);
		$nb_numeros= [];
		foreach($resultat_requete as $magazine) {
			list($nom_pays,$nom_magazine_abrege)=explode('/',$magazine['publicationcode']);
			$nb_numeros[$nom_magazine_abrege]=$magazine['cpt'];
		}
		return $nb_numeros;
	}

	static function get_issues_from_storycode($story_code) {
		$requete='SELECT inducks_issue.publicationcode AS publicationcode, inducks_issue.issuenumber AS issuenumber '
				.'FROM inducks_issue '
				.'INNER JOIN inducks_entry ON inducks_issue.issuecode = inducks_entry.issuecode '
				.'INNER JOIN inducks_storyversion ON inducks_entry.storyversioncode = inducks_storyversion.storyversioncode '
				.'WHERE storycode = \''.$story_code.'\' '
				.'ORDER BY publicationcode, issuenumber';
		return Inducks::requete_select($requete);
	}
	static function get_magazines_ne_paraissant_plus($publication_codes) {
		$liste_magazines= [];
		foreach($publication_codes as $publicationcode) {
			$liste_magazines[]="'".$publicationcode."'";
		}
	   	$requete_get_ne_parait_plus='SELECT CONCAT(PaysAbrege,\'/\',NomAbrege) AS publicationcode, NeParaitPlus FROM magazines WHERE publicationcode IN ('.implode(',',$liste_magazines).')';
	   	$resultat_get_ne_parait_plus=DM_Core::$d->requete_select_distante($requete_get_ne_parait_plus);

		$magazines_ne_paraissant_plus= [];
		foreach($resultat_get_ne_parait_plus as $resultat) {
			if ($resultat['NeParaitPlus']==1) {
				$magazines_ne_paraissant_plus[]=$resultat['publicationcode'];
			}
		}
	   	return $magazines_ne_paraissant_plus;
	}
}

require_once('ServeurDb.class.php');
Inducks::$use_local_db = ServeurDb::isServeurVirtuel();

if (isset($_POST['get_pays'])) {
	$liste_pays_courte=Inducks::get_pays();

	if ($_POST['inclure_tous_pays']) {
		?><option id="all"><?=TOUS_PAYS?><?php
	}
	$selected = isset($_POST['selected']) ? $_POST['selected'] : 'fr';

	foreach($liste_pays_courte as $id=>$pays) {
		if ($selected && $id === $selected)
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
	$resultats= [];
	$regex_num_alternatif='#([A-Z]+)([0-9]+)#';
	$numero_alternatif=preg_match($regex_num_alternatif, $_POST['numero']) == 0 ? null : preg_replace($regex_num_alternatif, '$1[ ]*$2', $_POST['numero']);
	$liste_magazines= [];
	$_POST['numero']=str_replace(' ','',$_POST['numero']);
	$_POST['magazine']=strtoupper($_POST['magazine']);
	$requete_get_extraits='SELECT sitecode, position, url FROM inducks_issue '
						 .'INNER JOIN inducks_entry ON inducks_issue.issuecode = inducks_entry.issuecode '
						 .'INNER JOIN inducks_entryurl ON inducks_entry.entrycode = inducks_entryurl.entrycode '
						 .'WHERE inducks_issue.publicationcode = \''.$_POST['pays'].'/'.$_POST['magazine'].'\' '
						 .'AND (REPLACE(issuenumber,\' \',\'\') = \''.$_POST['numero'].'\' '.(is_null($numero_alternatif) ? '':'OR REPLACE(issuenumber,\' \',\'\') REGEXP \''.$numero_alternatif.'\'').') '
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
			$resultats[]= ['page'=>$num_page,'url'=>$url];
		}
	}
	if (count($resultat_get_extraits) == 0)
		$resultats['cover']='images/cover_not_found.png';

	echo header("X-JSON: " . json_encode($resultats));
}
elseif (isset($_POST['get_magazines_histoire'])) {
	$nom_histoire=str_replace('"','\\"',Util::supprimerAccents(utf8_decode($_POST['histoire'])));
	$liste_magazines= [];
	if (strpos($nom_histoire, 'code=') === 0) {
		$liste_magazines['direct']=true;
		$code=substr($nom_histoire, strlen('code='));
		$resultat_requete=Inducks::get_issues_from_storycode($code);
		if (count($resultat_requete) == 0) {
			// On r�essaie en testant le doublement de chacun des espaces du code d'histoire
			$i=1;
			$code_avec_espaces = Util::remplacerNiemeCaractere($i, ' ', '  ', $code);
			while ($code_avec_espaces !== $code) {
				$resultat_requete = Inducks::get_issues_from_storycode($code_avec_espaces);
				if (count($resultat_requete) > 0)
					break;
				$i++;
				$code_avec_espaces = Util::remplacerNiemeCaractere($i, ' ', '  ', $code);
			}
		}

		$publication_codes= [];
		foreach($resultat_requete as $resultat) {
			$publication_codes[]=$resultat['publicationcode'];
		}
		list($noms_pays,$noms_magazines) = Inducks::get_noms_complets($publication_codes);
		foreach($resultat_requete as $resultat) {
			list($pays,$magazine)=explode('/',$resultat['publicationcode']);
			$nom_complet_magazine=$noms_magazines[$resultat['publicationcode']];
			$issuenumber=$resultat['issuenumber'];
			$liste_magazines[]= ['pays'=>$pays,
									 'magazine_numero'=>$magazine.'.'.$issuenumber,
									 'nom_magazine'=>$nom_complet_magazine,
									 'titre'=>$nom_complet_magazine];
		}
	}
	else {
		$condition = 'MATCH(inducks_entry.title) AGAINST (\''.implode(',', explode(' ', $nom_histoire)).'\')';
		$requete='SELECT DISTINCT inducks_storyversion.storycode AS storycode, inducks_entry.title AS title, '
							     .$condition.' AS score '
				.'FROM inducks_entry '
				.'INNER JOIN inducks_storyversion ON inducks_entry.storyversioncode = inducks_storyversion.storyversioncode '
				.'WHERE '.$condition.' '
				.'ORDER BY score DESC, title';
		$resultat_requete=Inducks::requete_select($requete);
		foreach($resultat_requete as $resultat) {
			$code=$resultat['storycode'];
			$title=$resultat['title'];
			$liste_magazines[]= ['code'=>$code,
									 'titre'=>$title];
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