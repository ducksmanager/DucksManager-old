<?php
if (isset($_GET['dbg'])) {
	error_reporting(E_ALL);
}
$database=isset($_GET['db']) ? $_GET['db'] : 'coa';

include_once('auth.php');

if (isset($_GET['req'])) {
	$requete=str_replace("\'","'",$_GET['req']);
	$resultats_tab= [];
	$resultats=Database::$handle->query($requete);
	$debut=true;
	$champs= [];
	while($resultat = $resultats->fetch_array(MYSQLI_ASSOC)) {
		if ($debut) {
			foreach(array_keys($resultat) as $cle)
				if (!is_int($cle))
					$champs[]=$cle;
			$debut=false;
		}
		$valeurs= [];
		foreach($resultat as $cle=>$valeur)
			if (!is_int($cle))
				$valeurs[$cle]=$valeur;
		$resultats_tab[]=$valeurs;
	}
	$resultats_tab= [$champs,$resultats_tab];
	if (isset($_GET['debug'])) {
		?><pre><?php echo $requete."\n";print_r($resultats_tab);?></pre><?php
	}
	else {
		echo serialize($resultats_tab);
	}
	mysqli_close(Database::$handle);
}
else
	echo 'Pas de requete';