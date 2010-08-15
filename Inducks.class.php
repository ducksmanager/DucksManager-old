<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
class Inducks {
	static $noms_complets;

	static function get_auteur($nom_auteur_abrege) {
		$regex_auteur='#<font size=\+3><b><img[^>]+>[^&]*&nbsp; ([^<]+)</b></font>#isu';
		$url='http://coa.inducks.org/creator.php?c='.$nom_auteur_abrege;
		$page=Util::get_page($url);
		preg_match($regex_auteur,$page,$auteur);
		return $auteur[1];
	}

	static function get_numeros($pays,$magazine) {
		$regex_magazine='#<a href=issue.php\?c='.$pays.'%2f'.$magazine.'[+]*([^>]*)>([^<]*)</a>#is';
		$url='http://coa.inducks.org/publication.php?c='.$pays.'/'.$magazine;
		$handle = @fopen($url, "r");
		if ($handle) {
			$buffer="";
		   	while (!feof($handle)) {
		     	$buffer.= fgets($handle, 4096);
		   	}
		   	fclose($handle);
		}
		else {
			echo ERREUR_CONNEXION_INDUCKS;
			return false;
		}
		preg_match_all($regex_magazine,$buffer,$numeros);
		foreach ($numeros as $indice=>$numero) {
			$numeros[$indice]=preg_replace($regex_magazine,'$1',$numero);
		}
		return $numeros[0];
	}

	function get_pays() {
		$url='http://coa.inducks.org/legend-country.php?xch=1&lg=4';
		$handle = @fopen($url, "r");
		if ($handle) {
			$buffer="";
		   	while (!feof($handle)) {
		     	$buffer.= fgets($handle, 4096);
		   	}
		   	fclose($handle);
		}
		else {
			echo ERREUR_CONNEXION_INDUCKS;
			return false;
		}
		$regex_pays='#<a href=country\.php\?c=([^>]+)>([^<]+)</a>#i';
		preg_match_all($regex_pays,$buffer,$liste_pays);
		$liste_pays_courte=array();
		foreach($liste_pays[0] as $pays) {
			$liste_pays_courte[preg_replace($regex_pays,'$1',$pays)]=preg_replace($regex_pays,'$2',$pays);
		}
		array_multisort($liste_pays_courte,SORT_STRING);
		return $liste_pays_courte;
	}

    static function get_nom_complet_magazine($pays,$magazine) {
        include_once('Database.class.php');
        $d=new Database();
        $requete_nom_complet_magazine='SELECT NomComplet FROM magazines WHERE (PaysAbrege LIKE "'.$pays.'" AND NomAbrege LIKE "'.$magazine.'")';
		$resultat_nom_complet_magazine=$d->requete_select($requete_nom_complet_magazine);
		if (!array_key_exists(0,$resultat_nom_complet_magazine)) {
            $liste_magazines=Inducks::get_noms_complets_magazines($pays);
            return $liste_magazines[$magazine];
        }
        else
            return utf8_encode($resultat_nom_complet_magazine[0]['NomComplet']);
    }

	static function get_noms_complets_magazines($pays) {
		global $codes_inducks;
		if (!is_array(self::$noms_complets))
			self::$noms_complets=array('?'=>'?');
		if (array_key_exists($pays,self::$noms_complets)) return self::$noms_complets[$pays];
		$adresse_pays='http://coa.inducks.org/country.php?c='.$pays.'&lg='.$codes_inducks[$_SESSION['lang']];
		$handle = @fopen($adresse_pays, "r");
		if ($handle) {
			$buffer="";
		   	while (!feof($handle)) {
		     	$buffer.= fgets($handle, 4096);
		   	}
		   	fclose($handle);
		}
		else {
			echo ERREUR_CONNEXION_INDUCKS;
		}
		$regex_magazine='#<a href="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</a>&nbsp;#is';
		$regex_pays='#"">([^:]+): publications</h1>#is';
		preg_match($regex_pays,$buffer,$nom_pays_recup);
		$nom_pays=preg_replace($regex_pays,'$1',$nom_pays_recup);
		preg_match_all($regex_magazine,$buffer,$pays_recup);
		$d = new Database();
                $requete_nom_pays='INSERT INTO pays(NomAbrege, NomComplet) VALUES ("'.$pays.'", "'.utf8_decode($nom_pays[0]).'")';
                $d->requete($requete_nom_pays);
		foreach($pays_recup[0] as $i=>$p) {
                    $requete_noms_magazines='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet) VALUES ("'.$pays.'","'.preg_replace($regex_magazine,'$1',$p).'","'.str_replace('"','',utf8_decode(preg_replace($regex_magazine,'$2',$p))).'")';
                    $d->requete($requete_noms_magazines);
		}
	}

	function get_magazines($pays) {
		$url='http://coa.inducks.org/country.php?xch=1&lg=4&c='.$pays;
		$handle = @fopen($url, "r");
		if ($handle) {
			$buffer="";
		   	while (!feof($handle)) {
		     	$buffer.= fgets($handle, 4096);
		   	}
		   	fclose($handle);
		}
		else {
			echo ERREUR_CONNEXION_INDUCKS;
			return false;
		}
		$regex_magazines='#<a href="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</a>&nbsp;#is';
		preg_match_all($regex_magazines,$buffer,$liste_magazines);
		$liste_magazines_courte=array();
		foreach($liste_magazines[0] as $magazine) {
			$liste_magazines_courte[preg_replace($regex_magazines,'$1',$magazine)]=preg_replace($regex_magazines,'$2',$magazine);//, "ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½", "aaaaaaooooooeeeeciiiiuuuun");;
		}
		array_multisort($liste_magazines_courte,SORT_STRING);
		//sort($liste_pays_courte);
		foreach($liste_magazines_courte as $id=>$magazine) {
			echo '<option id="'.$id.'">'.$magazine;
		}
	}

    static function afficher_form_inducks() {
        ?>
        <h2><?=SYNCHRONISATION_COMPTES?></h2>
        <a href="http://www.coa.inducks.org">Inducks</a> <?=INTRO_SYNCHRO_INDUCKS_1?><br />
        <?=INTRO_SYNCHRO_INDUCKS_2?><br />
        <?=INTRO_SYNCHRO_INDUCKS_3?><br />
        <br />
        <?=ENTREZ_IDENTIFIANTS_INDUCKS?>.<br /><br />
        <?php
        if (!isset($_SESSION['user'])) {
            ?><span style="color:red"><?=ATTENTION_MOT_DE_PASSE_INDUCKS?></span><br /><?php
        }
        ?>
        <form method="post" action="index.php?action=inducks">
            <table border="0">
                <tr>
                    <td><?=UTILISATEUR_INDUCKS?> :</td>
                    <td><input type="text" name="user" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?=MOT_DE_PASSE_INDUCKS?> :
                    </td>
                    <td>
                        <input type="password" name="pass" />
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
                        <input type="submit" value="<?=CONNEXION?>"/>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }

    static function appel() {
        //$user_pcent=rawurlencode($_POST['user']);
        //$pass_pcent=rawurlencode($_POST['pass']);
        $user_urled=urlencode($_POST['user']);
        $pass_urled=urlencode($_POST['pass']);
        $data = urlencode('login='.$user_pcent.'&pass='.$pass_pcent.'&redirect=collection.php');

        return '\''.$_POST['user'].'\',\''.$_POST['pass'].'\',\''.$user_urled.'\',\''.$pass_urled.'\',\''.$data.'\',\'POST\',\'http://coa.inducks.org/collection.php\',\'coa-preferred-language=4\',\'coa.inducks.org\'';
        //return '\''.$_POST['user'].'\',\''.$_POST['pass'].'\'';
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
    include_once('Database.class.php');
    $d=new Database();
    $requete_couverture_stockee='SELECT URL FROM couvertures WHERE Pays LIKE \''.$_POST['pays'].'\' AND Magazine LIKE \''.$_POST['magazine'].'\' AND Numéro LIKE \''.$_POST['numero'].'\'';
    $resultat_couverture_stockee=$d->requete_select($requete_couverture_stockee);
    if (count($resultat_couverture_stockee)!=0) {
        echo $resultat_couverture_stockee[0]['URL'];
        exit(0);
    }
    $nb_plus=7-strlen($_POST['numero'])-strlen($_POST['magazine']);
    $regex_image='#<td><img src="([^"]+)"><tr><td[^>]+><small>[^<]+<a href=\'http://outducks.org\'>outducks.org</a>#is';
    $adresse_numero='http://coa.inducks.org/issue.php?c='.$_POST['pays'].'%2F'.$_POST['magazine'];
    for ($i=0;$i<$nb_plus;$i++)
        $adresse_numero.='+';
    $adresse_numero.=$_POST['numero'];
    $handle = @fopen($adresse_numero, "r");
    if ($handle) {
        $buffer="";
        while (!feof($handle)) {
            $buffer.= fgets($handle, 4096);
        }
        fclose($handle);
        if (strpos($buffer, 'Issue not found')!==false) {
            $adresse_numero='http://coa.inducks.org/issue.php?c='.$_POST['pays'].'%2F'.$_POST['magazine'];
            for ($i=0;$i<$nb_plus+1;$i++)
                $adresse_numero.='+';
            $adresse_numero.=$_POST['numero'];
            $handle = @fopen($adresse_numero, "r");
            if ($handle) {
                $buffer="";
                while (!feof($handle)) {
                    $buffer.= fgets($handle, 4096);
                }
                fclose($handle);
            }
        }
		if (preg_match($regex_image,$buffer,$code_image)==0)
            $url='images/cover_not_found.png';
        else
            $url=$code_image[1];
        $requete_ajout_couverture='INSERT INTO couvertures(Pays,Magazine,Numéro,URL) '
                                 .'VALUES (\''.$_POST['pays'].'\',\''.$_POST['magazine'].'\',\''.$_POST['numero'].'\',\''.$url.'\')';
        $d->requete($requete_ajout_couverture);
        echo $url;
    }
    else {
        echo ERREUR_CONNEXION_INDUCKS;
    }
}
?>