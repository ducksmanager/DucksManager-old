<?php
if (isset($_GET['dbg'])) {
	error_reporting(E_ALL);
}
$database=isset($_GET['db']) ? $_GET['db'] : 'coa';

include_once('auth.php');

if (isset($_GET['req'])) {
	$requete=str_replace("\'","'",$_GET['req']);
	$resultats_tab=array();
	$resultats=mysql_query($requete);
	$debut=true;
	$champs=array();
	while($resultat = mysql_fetch_array($resultats)) {
		if ($debut) {
			foreach(array_keys($resultat) as $cle)
				if (!is_int($cle))
					$champs[]=$cle;
			$debut=false;
		}
		$valeurs=array();
		foreach($resultat as $cle=>$valeur)
			if (is_int($cle))
				$valeurs[$cle]=$valeur;
		$resultats_tab[]=$valeurs;
	}
	$resultats_tab=array($champs,$resultats_tab);
	if (isset($_GET['debug'])) {
		?><pre><?php echo $requete."\n";print_r($resultats_tab);?></pre><?php
	}
	else {
		echo serialize($resultats_tab);
	}
	mysql_close();
}
else
	echo 'Pas de requete';