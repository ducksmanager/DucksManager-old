<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once('locales/lang.php');
require_once('Database.class.php');
require_once('Inducks.class.php');
Util::exit_if_not_logged_in();

class Stats {
	static function getPublicationData() {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$counts= [];
		$total=0;
		$requete_cpt_numeros_groupes='SELECT Pays,Magazine,Count(Numero) AS cpt '
			.'FROM numeros '
			.'WHERE ID_Utilisateur='.$id_user.' '
			.'GROUP BY Pays,Magazine '
			.'ORDER BY cpt';
		$resultat_cpt_numeros_groupes=DM_Core::$d->requete_select($requete_cpt_numeros_groupes);

		$publication_codes= [];
		foreach($resultat_cpt_numeros_groupes as $resultat) {
			$publicationcode=$resultat['Pays'].'/'.$resultat['Magazine'];
			$cpt=intval($resultat['cpt']);
			$counts[$publicationcode]=$cpt;
			$total+=$cpt;
			$publication_codes[]=$publicationcode;
		}
		list($noms_pays,$noms_magazines)=Inducks::get_noms_complets($publication_codes);

		$autres=0;
		$nb_magazines_autres=0;
		$data = [];
		$labels = [];
		$colors = [];
		foreach($counts as $publicationcode=>$cpt) {
			if (!array_key_exists($publicationcode, $noms_magazines)) { // Magazine ayant disparu d'Inducks
				continue;
			}
			$nom_complet_magazine=$noms_magazines[$publicationcode];
			if ($cpt/$total<0.01) {
				$autres+=$cpt;
				$nb_magazines_autres++;
			}
			else {
				$data[]=$cpt;
				$labels[]=$nom_complet_magazine;
				$colors[]= sprintf('#%06X', mt_rand(0, 0xFFFFFF));
			}
		}
		if ($autres > 0) {
			$data[]=$autres;
			$labels[]=AUTRES.' ('.$nb_magazines_autres.' '.PUBLICATIONS__LOWERCASE.')';
			$colors[]= '#843598';
		}
		return ['values' => $data, 'colors' => $colors, 'labels' => $labels];
	}
}

if (isset($_POST['publications'])) {
	$data = Stats::getPublicationData();
	header("X-JSON: " . json_encode(array('values' => $data['values'], 'colors' => $data['colors'], 'labels' => $data['labels'])));
}