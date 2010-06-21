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
		$regex_magazine='#<A HREF="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</A>&nbsp;#is';
		$regex_pays='#; ([^:]+): publications</b></font>#is';
		$liste_magazines=array();
		preg_match($regex_pays,$buffer,$nom_pays_recup);
		$nom_pays=preg_replace($regex_pays,'$1',$nom_pays_recup);
		preg_match_all($regex_magazine,$buffer,$pays_recup);
		$requete_noms_magazines='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet) VALUES ';
		$debut=true;
		foreach($pays_recup[0] as $p) {
			if (!$debut) $requete_noms_magazines.=',';
			$requete_noms_magazines.='("'.$pays.'","'.preg_replace($regex_magazine,'$1',$p).'","'.str_replace('"','',utf8_decode(preg_replace($regex_magazine,'$2',$p))).'")';
			$liste_magazines[preg_replace($regex_magazine,'$1',$p)]=preg_replace($regex_magazine,'$2',$p).' ('.$nom_pays[0].')';
			$debut=false;
		}
		if ($_SESSION['lang']=='fr') {
			$d = new Database();
			$d->requete($requete_noms_magazines);
		}
		self::$noms_complets[$pays]=$liste_magazines;
		return $liste_magazines;
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
		$regex_magazines='#<A HREF="publication\.php\?c='.$pays.'/([^"]+)">([^<]+)</A>&nbsp;#is';
		preg_match_all($regex_magazines,$buffer,$liste_magazines);
		$liste_magazines_courte=array();
		foreach($liste_magazines[0] as $magazine) {
			$liste_magazines_courte[preg_replace($regex_magazines,'$1',$magazine)]=preg_replace($regex_magazines,'$2',$magazine);//, "��������������������������", "aaaaaaooooooeeeeciiiiuuuun");;
		}
		array_multisort($liste_magazines_courte,SORT_STRING);
		//sort($liste_pays_courte);
		foreach($liste_magazines_courte as $id=>$magazine) {
			echo '<option id="'.$id.'">'.$magazine;
		}
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
else if (isset($_POST['get_magazines'])) {
	Inducks::get_magazines($_POST['pays']);
}
else if (isset($_POST['get_numeros'])) {
	Inducks::get_numeros($_POST['pays'],$_POST['magazine']);
}
?>