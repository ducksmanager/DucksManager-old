<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include_once ('Util.class.php');
include_once('Database.class.php');
class Inducks {
	static $noms_complets;

	static function get_auteur($nom_auteur_abrege) {
            $regex_auteur='#<font size=\+3><b><img[^>]+>[^&]*&nbsp; ([^<]+)</b></font>#isu';
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
        
	static function get_numeros($pays,$magazine,$get_url=false) {
            $magazine_depart=$magazine;
            $magazine=Inducks::get_vrai_magazine($pays,$magazine);
            $regex_numero='#<a href=issue.php\?c='.$pays.'%2F'.$magazine_depart.'[+]*([^>]*)>[^<]*</a>([^<\(\)]*)#is';
            $regex_url_numero='#<a href=(issue.php\?c='.$pays.'%2F'.$magazine_depart.'[+]*([^>]*))>[^<]*</a>#is';
            $url='http://coa.inducks.org/publication.php?c='.$pays.'/'.$magazine;
            $page=Util::get_page($url);
            if ($get_url===true) {
                preg_match_all($regex_url_numero,$page,$numeros);
                $numeros[2]=array_map('nettoyer_numero',$numeros[2]);
                return array($numeros[1],$numeros[2]);
            }
            else {
                preg_match_all($regex_numero,$page,$numeros);
                $numeros[1]=array_map('nettoyer_numero',$numeros[1]);
                return array($numeros[1],$numeros[2]);
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
            $url='http://coa.inducks.org/legend-country.php?xch=1&lg='.Lang::$codes_inducks[$_SESSION['lang']];
            $page=Util::get_page($url);
            
            $regex_pays='#<a href=country\.php\?c=([^>]+)>([^<]+)</a>#i';
            preg_match_all($regex_pays,$page,$liste_pays);
            $liste_pays_courte=array();
            foreach($liste_pays[0] as $pays) {
                $nom_pays_court=preg_replace($regex_pays,'$1',$pays);
                $nom_pays=preg_replace($regex_pays,'$2',$pays);
                $liste_pays_courte[$nom_pays_court]=$nom_pays;
                $requete_nom_pays='INSERT INTO pays(NomAbrege, NomComplet,L10n) VALUES ("'.$nom_pays_court.'", "'.$nom_pays.'","'.$_SESSION['lang'].'")';
                DM_Core::$d->requete($requete_nom_pays);
            }
            array_multisort($liste_pays_courte,SORT_STRING);
            return $liste_pays_courte;
	}

        static function get_magazines_ne_paraissent_plus() {
            $page=Util::get_page('http://coa.inducks.org/inducks/isv/inducks_publicationcategory.isv');
            $tab=str_getcsv($page, '^');
            $tab2=array();
            for ($i=2;$i<count($tab)-1;$i+=2) {
                $regex_pays_magazine='#([a-z]+)/([A-Z0-9]+)#is';
                preg_match($regex_pays_magazine,$tab[$i],$pays_magazine);
                $tab2[$i-2]=array('Pays'=>$pays_magazine[1],'Magazine'=>$pays_magazine[2]);
                DM_Core::$d->requete($requete_neparaitplus);
                echo $requete_neparaitplus;
            }
            echo '<pre>';print_r($tab2);echo '</pre>';
        }

    static function get_nom_complet_magazine($pays,$magazine) {
        $requete_nom_complet_magazine='SELECT NomComplet FROM magazines WHERE (PaysAbrege LIKE "'.$pays.'" AND NomAbrege LIKE "'.$magazine.'")';
        $resultat_nom_complet_magazine=DM_Core::$d->requete_select($requete_nom_complet_magazine);
        if (!array_key_exists(0,$resultat_nom_complet_magazine)) {
            $liste_magazines=Inducks::get_noms_complets_magazines($pays);
            return $liste_magazines[$magazine];
        }
        else
            return $resultat_nom_complet_magazine[0]['NomComplet'];
    }

    static function get_noms_complets_magazines($pays) {
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
                if ($pays_recup[1][$i]=='JP') {
                    $a=1;
                }
                DM_Core::$d->requete($requete_noms_magazines);
            }
    }

    static function get_liste_magazines($pays) {
        $url='http://coa.inducks.org/country.php?xch=1&lg=4&c='.$pays;
        $buffer=Util::get_page($url);
        
        $regex_magazines='#<a href="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</a>&nbsp;#is';
        preg_match_all($regex_magazines,$buffer,$liste_magazines);
        $liste_magazines_courte=array();
        foreach($liste_magazines[0] as $magazine) {
                $liste_magazines_courte[preg_replace($regex_magazines,'$1',$magazine)]=preg_replace($regex_magazines,'$2',$magazine);//, "ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½", "aaaaaaooooooeeeeciiiiuuuun");;
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
        list($urls,$numeros)=Inducks::get_numeros($pays, $magazine,true);
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
    $regex_extrait='#<img border=0 src=\'(?:hr\.php\?image=)?(http://outducks.org/(?:(?:(?:(?:thumbnails2?/)?(?:webusers/(?:webusers/)?)|(?:renamed/'.$_POST['pays'].'/))[0-9A-Za-z]+/[0-9A-Za-z]+/'.$_POST['pays'].'_'.strtolower($_POST['magazine']).'_[^p]+p([0-9]+)_001)|(?:'.$_POST['pays'].'/'.strtolower($_POST['magazine']).'/'.$_POST['pays'].'_'.strtolower($_POST['magazine']).'_))[^\'&]+)(?:[^\']+)?\'>#is';
    
    $resultats=array();
    if (preg_match_all($regex_extrait,$page,$codes_images)>0) {
        for($i=0;$i<count($codes_images[0]);$i++) {
            $num_page=empty($codes_images[2][$i])?(-99+$i):intval($codes_images[2][$i]);
            $resultats[]=array('page'=>$num_page,'url'=>$codes_images[1][$i]);
        }
    }
    $resultats['cover']=$url;
    echo header("X-JSON: " . json_encode($resultats));
}
elseif (isset($_POST['get_covers'])) {
    echo header("X-JSON: " . json_encode(Inducks::get_covers($_POST['pays'], $_POST['magazine'])));
}
elseif (isset($_POST['get_magazines_histoire'])) {
    $nom_histoire=Util::supprimerAccents(utf8_decode($_POST['histoire']));
    echo $nom_histoire."\n";
    if (strpos($nom_histoire, 'code=') === 0) {
        $url='http://coa.inducks.org/story.php?c='.urlencode(substr($nom_histoire, strlen('code_')));
        $page_histoire=Util::get_page($url);
    }
    else {
        $url='http://coa.inducks.org/simp.php?d2='.urlencode($nom_histoire).'&kind=n';
        $page=Util::get_page($url);
    }
    echo $url."\n";
    if (strpos($nom_histoire, 'code=') !== 0) {
        $regex_redirection='#<meta[^;]+;url=([^"]+)"></meta>#is';
        preg_match($regex_redirection, $page,$url_redirect);
        $url_redirect=$url_redirect[1];
        $url2='http://coa.inducks.org/'.$url_redirect;
        $page_histoire=Util::get_page($url2);
        echo $url_redirect."\n";
        echo $page_histoire;
    }
    $regex_magazines='#<li><a href="issue\.php\?c=([^/]+)/([^\#"]+)[^"]*"(?:\#[^"]*")?>((?:(?:<span[^>]*>(?:[^<]+)</span>)?(?:[^<]*))*)#is';
    $trouve=preg_match_all($regex_magazines, $page_histoire,$magazines) > 0;
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
        $regex_histoire='#<a href="story\.php\?c=([^"]+)"><font[^>]+>[^<]+</font></a> </td>[^<]*<td>(?:<small>(?:<a[^>]*>[^<]*(?:<span[^>]+>[^<]*</span>)?[^<]*</a>,? ?)*</small><br>)?[^<]*<i>((?:(?:<span[^>]*>(?:[^<]+)</span>)?(?:[^<]*))*)</i>#is';
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
?>