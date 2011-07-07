<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once('locales/lang.php');
include_once('Util.class.php');
include_once('Database.class.php');
class Inducks {
	static $noms_complets;
		static $use_db=true;
		static $use_local_db=false;

		static function requete_select($requete) {
			if (Inducks::$use_local_db) {
				mysql_select_db('coa');
				$resultat = DM_Core::$d->requete_select($requete);
				mysql_select_db(DatabasePriv::$nom_db_DM);
				return $resultat;
			}
			else {
				list($champs,$resultats)=unserialize(Util::get_page(DatabasePriv::$url_serveur_virtuel.'/sql.php?req='.urlencode($requete)));
				foreach($champs as $i_champ=>$nom_champ) {
					foreach($resultats as $i=>$resultat) {
						$resultats[$i][$nom_champ]=$resultat[$i_champ];
					}
				}
			}
			return $resultats;
		}

	static function get_auteur($nom_auteur_abrege) {
			if (self::$use_db) {
				$requete='SELECT fullname FROM inducks_person WHERE personcode LIKE \''.$nom_auteur_abrege.'\'';
				$resultat_requete = Inducks::requete_select($requete);
				return $resultat_requete[0]['fullname'];
			}
			$regex_auteur='#<h1><img[^>]*>([^<]+)</h1>#isu';
			$url='http://coa.inducks.org/creator.php?c='.$nom_auteur_abrege;
			$page=Util::get_page($url);
			preg_match($regex_auteur,$page,$auteur);
			return $auteur[1];  
	}

		static function get_vrai_magazine($pays,$magazine) {
			$requete_get_redirection='SELECT NomAbrege FROM magazines WHERE PaysAbrege LIKE \''.$pays.'\' AND RedirigeDepuis LIKE \''.$magazine.'\'';
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
		
	static function get_numeros($pays,$magazine,$get_url=false,$sans_espace=false) {
			$magazine_depart=$magazine;
			$magazine=Inducks::get_vrai_magazine($pays,$magazine);
			$fonction_nettoyage=$sans_espace ? 'nettoyer_numero_sans_espace' : 'nettoyer_numero';

			if (self::$use_db) {
				$numeros=array();
				if ($get_url===true) {
					$urls=array();
					$requete='SELECT issuenumber FROM inducks_issue WHERE publicationcode LIKE \''.$pays.'/'.$magazine.'\'';
					$resultat_requete=Inducks::requete_select($requete);
					foreach($resultat_requete as $i=>$numero) {
						$numeros[$i]=call_user_func($fonction_nettoyage, $numero['issuenumber']);
						$urls[$i]='issue.php?c='.$pays.'%2F'.$magazine_depart.str_replace(' ','+',$numero['issuenumber']);
					}
					return array($numeros,$urls);
				}
				else {
					$titres=array();
					$requete='SELECT issuenumber, title FROM inducks_issue WHERE publicationcode LIKE \''.$pays.'/'.$magazine.'\'';
					$resultat_requete=Inducks::requete_select($requete);
					foreach($resultat_requete as $i=>$numero) {
						$numeros[$i]=call_user_func($fonction_nettoyage, $numero['issuenumber']);
						$titres[$i]=$numero['title'];
					}
					return array($numeros,$titres);
				}
			}
			else {
				$regex_numero='#<a href=issue.php\?c='.$pays.'%2F'.$magazine_depart.'[+]*([^>]*)>[^<]*</a>([^<\(\)]*)#is';
				$regex_url_numero='#<a href=(issue.php\?c='.$pays.'%2F'.$magazine_depart.'[+]*([^>]*))>[^<]*</a>#is';
				$url='http://coa.inducks.org/publication.php?c='.$pays.'/'.$magazine;
				$page=Util::get_page($url);
				if ($get_url===true) {
					preg_match_all($regex_url_numero,$page,$numeros);
					$numeros[2]=array_map($fonction_nettoyage,$numeros[2]);
					return array($numeros[1],$numeros[2]);
				}
				else {
					preg_match_all($regex_numero,$page,$numeros);
					$numeros[1]=array_map($fonction_nettoyage,$numeros[1]);
					return array($numeros[1],$numeros[2]);
				}
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
		$requete='SELECT countrycode, countryname FROM inducks_countryname WHERE languagecode LIKE \''.$_SESSION['lang'].'\' ORDER BY countryname';
		$resultat_requete=Inducks::requete_select($requete);
		$liste_pays_courte=array();
		foreach($resultat_requete as $pays) {
			$liste_pays_courte[$pays['countrycode']]=$pays['countryname'];
		}
		return $liste_pays_courte;
	}

	static function get_nom_complet_magazine($pays,$magazine) {
		$requete_pays='SELECT countryname FROM inducks_countryname WHERE languagecode LIKE \''.$_SESSION['lang'].'\' AND countrycode LIKE \''.$pays.'\' ORDER BY countryname';
		$resultat_requete=Inducks::requete_select($requete_pays);
		$nom_pays=$resultat_requete[0]['countryname'];
			
		$requete_magazine='SELECT title FROM inducks_publication WHERE publicationcode LIKE \''.$pays.'/'.$magazine.'\'';
		$resultat_requete=Inducks::requete_select($requete_magazine);
		$nom_magazine=$resultat_requete[0]['title'];
			
		return array($nom_pays,$nom_magazine);
	}

	static function get_noms_complets_magazines($pays) {
		if (self::$use_db) {

			$requete='SELECT publicationcode, title FROM inducks_publication WHERE countrycode LIKE \''.$pays.'\'';
			$resultat_requete=Inducks::requete_select($requete);
			$liste=array();
			foreach($resultat_requete as $magazine) {
				list($nom_pays,$nom_magazine_abrege)=explode('/',$magazine['publicationcode']);
				$liste[$nom_magazine_abrege]=$magazine['title'];$requete_noms_magazines='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet) VALUES ("'.$pays.'","'.$nom_magazine_abrege.'","'.str_replace('"','',$magazine['title']).'")';
				DM_Core::$d->requete($requete_noms_magazines);
			}
			return $liste;
		}
		if (!is_array(self::$noms_complets))
			self::$noms_complets=array('?'=>'?');
		if (array_key_exists($pays,self::$noms_complets)) return self::$noms_complets[$pays];
		$adresse_pays='http://coa.inducks.org/country.php?xch=1&c='.$pays.'&lg='.Lang::$codes_inducks[$_SESSION['lang']];
		$buffer=Util::get_page($adresse_pays);

		$regex_magazine='#<a href="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</a>&nbsp;#is';
		$regex_pays='#"">([^:]+): publications</h1>#is';
		preg_match($regex_pays,$buffer,$nom_pays_recup);
		$nom_pays=preg_replace($regex_pays,'$1',$nom_pays_recup);
		preg_match_all($regex_magazine,$buffer,$pays_recup);
		$requete_nom_pays='INSERT INTO pays(NomAbrege, NomComplet,L10n) VALUES ("'.$pays.'", "'.$nom_pays[0].'","'.$_SESSION['lang'].'")';
		DM_Core::$d->requete($requete_nom_pays);
		foreach($pays_recup[0] as $i=>$p) {
			$requete_noms_magazines='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet) VALUES ("'.$pays.'","'.$pays_recup[1][$i].'","'.str_replace('"','',$pays_recup[2][$i]).'")';
			DM_Core::$d->requete($requete_noms_magazines);
		}
	}

	static function get_liste_magazines($pays) {
		if (self::$use_db) {
			$requete='SELECT publicationcode, title FROM inducks_publication WHERE countrycode LIKE \''.$pays.'\'';
			$resultat_requete=Inducks::requete_select($requete);
			$liste_magazines_courte=array();
			foreach($resultat_requete as $magazine) {
				list($nom_pays,$nom_magazine_abrege)=explode('/',$magazine['publicationcode']);
				$liste_magazines_courte[$nom_magazine_abrege]=$magazine['title'];
			}
			array_multisort($liste_magazines_courte,SORT_STRING);
			return $liste_magazines_courte;
		}
		$url='http://coa.inducks.org/country.php?xch=1&lg=4&c='.$pays;
		$buffer=Util::get_page($url);
		
		$regex_magazines='#<a href="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</a>&nbsp;#is';
		preg_match_all($regex_magazines,$buffer,$liste_magazines);
		$liste_magazines_courte=array();
		foreach($liste_magazines[0] as $magazine) {
			$liste_magazines_courte[preg_replace($regex_magazines,'$1',$magazine)]=preg_replace($regex_magazines,'$2',$magazine);
		}
		array_multisort($liste_magazines_courte,SORT_STRING);
		//sort($liste_pays_courte);
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
		if (self::$use_db) {
			$requete='SELECT publicationcode, Count(issuenumber) AS cpt FROM inducks_issue WHERE publicationcode LIKE \''.$pays.'/%\' GROUP BY publicationcode';
			$resultat_requete=Inducks::requete_select($requete);
			$nb_numeros=array();
			foreach($resultat_requete as $magazine) {
				list($nom_pays,$nom_magazine_abrege)=explode('/',$magazine['publicationcode']);
				$nb_numeros[$nom_magazine_abrege]=$magazine['cpt'];
			}
			return $nb_numeros;
		}
		$nb_numeros=array();
		$url='http://coa.inducks.org/country.php?xch=1&lg=4&c='.$pays;
		$page=Util::get_page($url);
		$regex_get_nb_numeros='#<a href="publication\.php\?c='.$pays.'/([^"]+)">[^<]+</a>[^<]*<i>\(([0-9]+) #isU';
		preg_match_all($regex_get_nb_numeros,$page,$liste_magazines);
		foreach(array_keys($liste_magazines[0]) as $i)
			$nb_numeros[$liste_magazines[1][$i]]=$liste_magazines[2][$i];
		return $nb_numeros;
	}
	static function numero_to_page($pays,$magazine,$numero) {
		$magazine=strtoupper($magazine);
		list($urls,$numeros)=Inducks::get_numeros($pays, $magazine,true,true);
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
	if (Inducks::$use_db) {
		$regex_num_alternatif='#([A-Z]+)([0-9]+)#';
		$numero_alternatif=preg_match($regex_num_alternatif, $_POST['numero']) == 0 ? null : preg_replace($regex_num_alternatif, '$1[ ]*$2', $_POST['numero']);
		$liste_magazines=array();
		$_POST['magazine']=strtoupper($_POST['magazine']);
		$requete_get_extraits='SELECT sitecode, position, url FROM inducks_issue '
							 .'INNER JOIN inducks_entry ON inducks_issue.issuecode = inducks_entry.issuecode '
							 .'INNER JOIN inducks_entryurl ON inducks_entry.entrycode = inducks_entryurl.entrycode '
							 .'WHERE inducks_issue.publicationcode LIKE \''.$_POST['pays'].'/'.$_POST['magazine'].'\' ' 
							 .'AND (issuenumber LIKE \''.$_POST['numero'].'\' '.(is_null($numero_alternatif) ? '':'OR issuenumber REGEXP \''.$numero_alternatif.'\'').') '
							 //.'AND inducks_entryurl.sitecode LIKE \'webusers\' '
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
	}
	else {
		$page=Inducks::numero_to_page($_POST['pays'], $_POST['magazine'], $_POST['numero']);

		$regex_cover='#<img src="(?:hr\.php\?normalsize=[\d]+&(?:amp;)?image=)([^"]+)" alt="HR" /><br />[^<]*<span class="infoImage">[^<]*<a href=\'http://outducks.org\'>outducks.org</a>#is';

		if (preg_match($regex_cover,$page,$code_image)==0)
			$url='images/cover_not_found.png';
		else {
			$url=$code_image[1];
			$requete_ajout_couverture='INSERT INTO couvertures(Pays,Magazine,Numero,URL) '
									 .'VALUES (\''.$_POST['pays'].'\',\''.$_POST['magazine'].'\',\''.$_POST['numero'].'\',\''.$url.'\')';
			DM_Core::$d->requete($requete_ajout_couverture);
		}
		$regex_extrait='#<img border="0" src="(?:hr\.php\?image=)?(http://outducks.org/(?:(?:(?:(?:thumbnails2?/)?(?:webusers/(?:webusers/)?)|(?:renamed/'.$_POST['pays'].'/))[0-9A-Za-z]+/[0-9A-Za-z]+/'.$_POST['pays'].'_'.strtolower($_POST['magazine']).'_[^p]+p([0-9]+)_001)|(?:'.$_POST['pays'].'/'.strtolower($_POST['magazine']).'/'.$_POST['pays'].'_'.strtolower($_POST['magazine']).'_))[^"&]+)(?:[^"]+)?"#is';

		if (preg_match_all($regex_extrait,$page,$codes_images)>0) {
			for($i=0;$i<count($codes_images[0]);$i++) {
				$num_page=empty($codes_images[2][$i])?(-99+$i):intval($codes_images[2][$i]);
				$resultats[]=array('page'=>$num_page,'url'=>$codes_images[1][$i]);
			}
		}
		$resultats['cover']=$url;
	}
	echo header("X-JSON: " . json_encode($resultats));
}
elseif (isset($_POST['get_covers'])) {
	echo header("X-JSON: " . json_encode(Inducks::get_covers($_POST['pays'], $_POST['magazine'])));
}
elseif (isset($_POST['get_magazines_histoire'])) {
	$nom_histoire=Util::supprimerAccents(utf8_decode($_POST['histoire']));
	echo $nom_histoire."\n";
	if (Inducks::$use_db) {
		$liste_magazines=array();
		mysql_select_db('coa');
		if (strpos($nom_histoire, 'code=') === 0) {
			$liste_magazines['direct']=true;
			$code=substr($nom_histoire, strlen('code='));
			$requete='SELECT inducks_issue.publicationcode AS publicationcode, inducks_issue.issuenumber AS issuenumber '
					.'FROM inducks_issue '
					.'INNER JOIN inducks_entry ON inducks_issue.issuecode = inducks_entry.issuecode '
					.'INNER JOIN inducks_storyversion ON inducks_entry.storyversioncode = inducks_storyversion.storyversioncode '
					.'WHERE storycode LIKE \''.$code.'\' '
					.'ORDER BY publicationcode, issuenumber';
			$resultat_requete=Inducks::requete_select($requete);
			foreach($resultat_requete as $resultat) {
				list($pays,$magazine)=explode('/',$resultat['publicationcode']);
				list($pays_complet,$magazine_complet)=DM_Core::$d->get_nom_complet_magazine($pays,$magazine);
				$issuenumber=$resultat['issuenumber'];
				$liste_magazines[]=array('pays'=>$pays,
										 'magazine_numero'=>$magazine.'.'.$issuenumber,
										 'nom_magazine'=>$magazine_complet,
										 'titre'=>$magazine_complet.' '.$issuenumber);
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
		mysql_select_db('db301759616');
	}
	else {
		if (strpos($nom_histoire, 'code=') === 0) {
			$url='http://coa.inducks.org/story.php?c='.urlencode(substr($nom_histoire, strlen('code_')));
			$page=Util::get_page($url);
		}
		else {
			$url='http://coa.inducks.org/simp.php?d2='.urlencode($nom_histoire).'&kind=n';
			$page=Util::get_page($url);
		}
		echo $url."\n";
		if (strpos($nom_histoire, 'code=') === 0) {
			$regex_redirection='#<meta[^;]+;url=([^"]+)"></meta>#is';
			preg_match($regex_redirection, $page,$url_redirect);
			if (isset($url_redirect[1])) {
				$url_redirect=$url_redirect[1];
				$url2='http://coa.inducks.org/'.$url_redirect;
				$page=Util::get_page($url2);
				echo $url_redirect."\n";
			}
		}
		$regex_magazines='#<li><a href="issue\.php\?c=([^/]+)/([^\#"]+)[^"]*"(?:\#[^"]*")?>((?:(?:<span[^>]*>(?:[^<]+)</span>)?(?:[^<]*))*)#is';
		$trouve=preg_match_all($regex_magazines, $page,$magazines) > 0;
		/* 1 : Pays ; 2 : Magazine+Numéro ; 3 : Titre */
		$liste_magazines=array();
		if ($trouve) { // Nom d'histoire direct
			for($i=0;$i<count($magazines[0]);$i++) {
				$titre_a_nettoyer=$magazines[3][$i];
				$regex_span='#<span[^>]+>([^<]*)</span>#is';
				preg_match_all($regex_span, $titre_a_nettoyer,$spans);
				for ($j=0;$j<count($spans[0]);$j++)
					$titre_a_nettoyer=str_replace ($spans[0][$j], $spans[1][$j], $titre_a_nettoyer);
				$titre=$titre_a_nettoyer;
				$liste_magazines[]=array('pays'=>$magazines[1][$i],
										 'magazine_numero'=>$magazines[2][$i],
										 'titre'=>$titre);
			}
			//usort($liste_magazines, 'trier_resultats_recherche');
			$liste_magazines['direct']=true;
		}
		else {
			$regex_histoire='#<a href="story\.php\?c=([^"]+)"><font[^>]+>[^<]+</font></a> </td>[^<]*<td>(?:<small>(?:<a[^>]*>[^<]*(?:<span[^>]+>[^<]*</span>)?[^<]*</a>,? ?)*</small><br/?>)?[^<]*<i>((?:(?:<span[^>]*>(?:[^<]+)</span>)?(?:[^<]*))*)</i>#is';
			preg_match_all($regex_histoire, $page,$histoires);
			$liste_magazines=array();
			for($i=0;$i<count($histoires[0]);$i++) {
				$titre_a_nettoyer=$histoires[2][$i];
				$regex_span='#<span[^>]+>([^<]*)</span>#is';
				preg_match_all($regex_span, $titre_a_nettoyer,$spans);
				for ($j=0;$j<count($spans[0]);$j++)
					$titre_a_nettoyer=str_replace ($spans[0][$j], $spans[1][$j], $titre_a_nettoyer);
				$titre=$titre_a_nettoyer;
				$liste_magazines[]=array('code'=>urldecode($histoires[1][$i]),
										 'titre'=>$titre);
			}
			//usort($liste_magazines, 'trier_resultats_recherche');
			if (count($liste_magazines) > 10) {
				$liste_magazines=array_slice($liste_magazines, 0,10);
				$liste_magazines['limite']=true;
			}
		}
	}
	echo header("X-JSON: " . json_encode($liste_magazines));
}

function trier_resultats_recherche ($a,$b) {
	if ($a['titre'] < $b['titre'])
		return -1;
	else
		return $a['titre'] == $b['titre'] ? 0 : 1;
}
		
function nettoyer_numero($numero) {
	$numero= str_replace("\n",'',preg_replace('#[+ ]+#is',' ',$numero));
	return $numero;
}
		
function nettoyer_numero_sans_espace($numero) {
	$numero= str_replace("\n",'',preg_replace('#[+ ]+#is','',$numero));
	return $numero;
}
?>