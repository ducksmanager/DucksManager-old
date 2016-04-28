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
	public static $id_user = null;

	static function getPublicationData() {
		$counts= [];
		$total=0;
		$resultat_cpt_numeros_groupes=DM_Core::$d->requete_select(
			'SELECT Pays,Magazine,Count(Numero) AS cpt
			 FROM numeros
			 WHERE ID_Utilisateur='.static::$id_user.'
			 GROUP BY Pays,Magazine
			 ORDER BY cpt'
		);

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

	static function getConditionData() {
		$resultat=DM_Core::$d->requete_select('
			SELECT Count(Numero) AS c
			FROM numeros
			WHERE ID_Utilisateur='.static::$id_user
		);

		$data = [];
		$labels = [];
		$colors = [];

		$total=$resultat[0]['c'];
		$autres=0;
		foreach(Database::$etats as $etat_court=>$infos_etat) {
			$resultat=DM_Core::$d->requete_select('
				SELECT Count(Numero) AS c
				FROM numeros
				WHERE ID_Utilisateur='.static::$id_user.'
				  AND Etat = \''.$etat_court.'\''
			);
			$cpt=$resultat[0]['c'];
			if ($cpt==0) continue;
			if ($cpt/$total<0.01) {
				$autres+=$cpt;
			}
			else {
				$data[]=$cpt;
				$labels[]=utf8_encode(Database::$etats[$etat_court][0]);
				$colors[]= sprintf('#%06X', mt_rand(0, 0xFFFFFF));

//				$tooltip = utf8_encode(Database::$etats[$etat_court][0].'<br>'.NUMEROS_POSSEDES).' : '.$cpt.' ('.round($cpt/$total).'%)';
			}
		}
		if ($autres!=0) {
			$data[]=$autres;
			$labels[]=utf8_encode(AUTRES);
			$colors[]= sprintf('#%06X', mt_rand(0, 0xFFFFFF));

//			$tooltip = utf8_encode(AUTRES.'<br>'.NUMEROS_POSSEDES).' : '.$autres.' ('.round($autres/$total).'%)';
		}
		return ['values' => $data, 'colors' => $colors, 'labels' => $labels];
	}

	static function getPossessionsData($for = null) {
		if (is_null($for)) {
			include_once ('locales/lang.php');
			foreach(array_keys($_POST) as $key)
				$_POST[$key] = str_replace('\\"','"',$_POST[$key]);
			$infos=json_decode($_POST['infos']);
			$donnees= [];

			$publication_codes= [];
			foreach(json_decode($_POST['ids']) as $i=>$pays) {
				foreach(array_keys(get_object_vars($infos[$i]->total)) as $magazine) {
					$publication_codes[]=$pays.'/'.$magazine;
				}
			}
			list($noms_complets_pays,$noms_complets_magazines)=Inducks::get_noms_complets($publication_codes);

			foreach(json_decode($_POST['ids']) as $i=>$pays) {
				foreach($infos[$i]->total as $magazine=>$total) {
					$pays_complet = $noms_complets_pays[$pays];
					$publication_code = $pays.'/'.$magazine;
					if (array_key_exists($publication_code, $noms_complets_magazines)) {
						$magazine_complet = $noms_complets_magazines[$pays.'/'.$magazine];
					}
					else { // Magazine ayant disparu d'Inducks
						$magazine_complet = $magazine;
					}
					$donnee=new stdClass ();
					$donnee->publication_code=$publication_code;

					$donnee->pays_court=$pays;
					$donnee->pays=$pays_complet;
					
					$donnee->nom_magazine_court=$magazine;
					$donnee->nom_magazine=$magazine_complet;
					
					$donnee->total=$total;
					$donnee->possede=$infos[$i]->possede->$magazine;
					$donnee->total_pct=$infos[$i]->total_pct->$magazine;
					$donnee->possede_pct=$infos[$i]->possede_pct->$magazine;
					$donnees[]=$donnee;
				}
			}
			$title = utf8_encode(POSSESSION_NUMEROS);

			$possedes = [
				'label' => utf8_encode(NUMEROS_POSSEDES),
				'backgroundColor' => '#FF8000',
				'data' => []
			];
			$possedes_cpt = $possedes;

			$totaux = [
				'label' => utf8_encode(NUMEROS_REFERENCES),
				'backgroundColor' => '#04B404',
				'data' => []
			];
			$totaux_cpt = $possedes;

			foreach ($donnees as $donnee) {
				$possedes['data'][] = $donnee->possede;
				$totaux['data'][] = intval($donnee->total)-$donnee->possede;
			}


			foreach ($donnees as $donnee) {
				$possedes_cpt['data'][] = $donnee->possede;
				$totaux_cpt['data'][] = intval($donnee->total)-$donnee->possede;
			}

			$supertotal=0;
			foreach($donnees as $donnee) {
				if ($donnee->total+$donnee->possede>$supertotal) {
					$supertotal=$donnee->total;
				}
			}

			$legend = [utf8_encode(NUMEROS_POSSEDES), utf8_encode(NUMEROS_REFERENCES)];

			$labels= [];
			$labels_pays_longs= [];
			$labels_magazines_longs= [];
			foreach($donnees as $donnee) {
				$labels[]=$donnee->publication_code;
				$labels_pays_longs[$donnee->pays_court]=$donnee->pays;
				$labels_magazines_longs[$donnee->publication_code]=$donnee->nom_magazine;
			}

			return [
				'datasets' => [$possedes, $totaux],
				'legend' => $legend,
				'labels' => $labels,
				'labels_magazines_longs' => $labels_magazines_longs,
				'labels_pays_longs' => $labels_pays_longs,
				'title' => $title,
			];
		}
		else {
			$id_user = static::$id_user;
			$l=DM_Core::$d->toList($id_user);

			$retour= ['total'=>null,'possede'=>null,'total_pct'=>null,'possede_pct'=>null];
			require_once('Inducks.class.php');
			$nb_numeros_magazines=Inducks::get_nb_numeros_magazines_pays($for);
			foreach(array_keys($l->collection[$for]) as $magazine) {
				if (array_key_exists($magazine,$nb_numeros_magazines)) {
					$retour['total'][$magazine]=$nb_numeros_magazines[$magazine];
					$retour['possede'][$magazine]=count($l->collection[$for][$magazine]);
					$retour['possede_pct'][$magazine]=round(100*($retour['possede'][$magazine]/$retour['total'][$magazine]));
					$retour['total_pct'][$magazine]=100-$retour['possede_pct'][$magazine];
				}
				else {
					$retour['total'][$magazine]=0;
					$retour['possede'][$magazine]=0;
					$retour['possede_pct'][$magazine]=0;
					$retour['total_pct'][$magazine]=0;
				}
			}
			return $retour;
		}
	}
}

Stats::$id_user=DM_Core::$d->user_to_id($_SESSION['user']);

if (isset($_POST['publications'])) {
	$data = Stats::getPublicationData();
	header("X-JSON: " . json_encode($data));
}
else if (isset($_POST['conditions'])) {
	$data = Stats::getConditionData();
	header("X-JSON: " . json_encode($data));
}
else if (isset($_POST['possessions'])) {
	if (isset($_POST['init_chargement'])) {
		$l=DM_Core::$d->toList(Stats::$id_user);
		header("X-JSON: " . json_encode(array_keys($l->collection)));
	}
	else if (isset($_POST['element'])) {
		header("X-JSON: " . json_encode(Stats::getPossessionsData($_POST['element'])));
	}
	else if (isset($_POST['fin'])) {
		$data = Stats::getPossessionsData(null);
		header("X-JSON: " . json_encode(Stats::getPossessionsData()));
	}
}