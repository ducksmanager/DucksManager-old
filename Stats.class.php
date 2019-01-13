<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once 'locales/lang.php';
require_once 'Database.class.php';
require_once 'Inducks.class.php';
Util::exit_if_not_logged_in();

class Stats {
	public static $id_user;

    static function stringToColor($str) {
        return '#'.substr(dechex(crc32($str)), 0, 6);
    }

	static function getPublicationData() {
		$counts= [];
		$total=0;
		$resultat_cpt_numeros_groupes=DM_Core::$d->requete(
            'SELECT Pays,Magazine,Count(Numero) AS cpt
			 FROM numeros
			 WHERE ID_Utilisateur=' . static::$id_user . '
			 GROUP BY Pays,Magazine
			 ORDER BY cpt desc'
		);

		$publication_codes= [];
		foreach($resultat_cpt_numeros_groupes as $resultat) {
			$publicationcode=$resultat['Pays'].'/'.$resultat['Magazine'];
			$cpt= (int)$resultat['cpt'];
			$counts[$publicationcode]=$cpt;
			$total+=$cpt;
			$publication_codes[]=$publicationcode;
		}
		$noms_magazines = Inducks::get_noms_complets_magazines($publication_codes);

		$autres=0;
		$nb_magazines_autres=0;
		$data = [];
		$labels = [];
		$colors = [];
		foreach($counts as $publicationcode=>$cpt) {
			if (array_key_exists($publicationcode, $noms_magazines)) {
                $nom_complet_magazine=$noms_magazines[$publicationcode];
                if ($cpt/$total<0.01) {
                    $autres+=$cpt;
                    $nb_magazines_autres++;
                }
                else {
                    $data[]=$cpt;
                    $labels[]=$nom_complet_magazine;
                    $colors[]= self::stringToColor($publicationcode);
                }
            }
		}
		if ($autres > 0) {
			$data[]=$autres;
			$labels[]=AUTRES.' ('.$nb_magazines_autres.' '.strtolower(PUBLICATIONS).')';
			$colors[]= '#000';
		}
		return ['values' => $data, 'colors' => $colors, 'labels' => $labels];
	}

	static function getConditionData() {
		$resultats=DM_Core::$d->requete('
			SELECT Etat, Count(Numero) AS cpt
			FROM numeros
			WHERE ID_Utilisateur=' . static::$id_user . '
            GROUP BY Etat DESC
            HAVING COUNT(Numero) > 0
		');

		$data = [];
		$labels = [];
		$colors = [];

		foreach($resultats as $resultat) {
		    if (array_key_exists($resultat['Etat'], Database::$etats)) {
                $data[]=$resultat['cpt'];
                list($labels[], $colors[])=Database::$etats[$resultat['Etat']];
            }
		}
		return ['values' => $data, 'colors' => $colors, 'labels' => $labels];
	}

	static function getPossessionsData($for = null) {
		if (is_null($for)) {
			include_once 'locales/lang.php';
			foreach(array_keys($_POST) as $key) {
                $_POST[$key] = str_replace('\\"', '"', $_POST[$key]);
            }
			$infos=json_decode($_POST['infos']);
			$donnees= [];

			$publication_codes= [];
			foreach(json_decode($_POST['ids']) as $i=>$pays) {
				foreach(array_keys(get_object_vars($infos[$i]->total)) as $magazine) {
					$publication_codes[]=$pays.'/'.$magazine;
				}
			}
			$noms_complets_pays = Inducks::get_noms_complets_pays($publication_codes);
			$noms_complets_magazines = Inducks::get_noms_complets_magazines($publication_codes);

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
					$donnee->possede_pct=$infos[$i]->possede_pct->$magazine;
					$donnees[]=$donnee;
				}
			}
			$title = POSSESSION_NUMEROS;

			$possedes = [
				'label' => NUMEROS_POSSEDES,
				'backgroundColor' => '#FF8000',
				'data' => []
			];
			$possedes_cpt = $possedes;

			$totaux = [
				'label' => NUMEROS_REFERENCES,
				'backgroundColor' => '#04B404',
				'data' => []
			];
			$totaux_cpt = $totaux;

			foreach ($donnees as $donnee) {
				$possedes['data'][] = $donnee->possede;
				$totaux['data'][] = (int)$donnee->total -$donnee->possede;
			}


			foreach ($donnees as $donnee) {
				$possedes_cpt['data'][] = $donnee->possede_pct;
				$totaux_cpt['data'][] = 100-$donnee->possede_pct;
			}

			$supertotal=0;
			foreach($donnees as $donnee) {
				if ($donnee->total+$donnee->possede>$supertotal) {
					$supertotal=$donnee->total;
				}
			}

			$legend = [NUMEROS_POSSEDES, NUMEROS_REFERENCES];

			$labels= [];
			$labels_pays_longs= [];
			$labels_magazines_longs= [];
			foreach($donnees as $donnee) {
				$labels[]=$donnee->publication_code;
				$labels_pays_longs[$donnee->pays_court]=$donnee->pays;
				$labels_magazines_longs[$donnee->publication_code]=$donnee->nom_magazine;
			}

			return [
				'datasets' => [
					'possedes' => $possedes, 'totaux' => $totaux,
					'possedes_cpt' => $possedes_cpt, 'totaux_cpt' => $totaux_cpt
				],
				'legend' => $legend,
				'labels' => $labels,
				'labels_magazines_longs' => $labels_magazines_longs,
				'labels_pays_longs' => $labels_pays_longs,
				'title' => $title,
			];
		}

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
            }
            else {
                $retour['total'][$magazine]=0;
                $retour['possede'][$magazine]=0;
                $retour['possede_pct'][$magazine]=0;
            }
        }
        return $retour;
    }
	
	static function getPurchaseHistory() {
		$id_user=static::$id_user;

		$requete_achats = "
			SELECT DATE_FORMAT(Date,'%Y-%m') AS Mois, CONCAT(Pays, '/', Magazine) AS Publicationcode, Count(Numero) AS cpt
			FROM numeros n
			  LEFT JOIN achats USING (ID_Acquisition)
			WHERE ID_Utilisateur=$id_user
			GROUP BY YEAR(Date), MONTH(Date), Pays,Magazine
			ORDER BY YEAR(Date), MONTH(Date)
		";

		$resultat_achats = DM_Core::$d->requete($requete_achats);

		$premier_achat = null;
		$achats_magazines_nouv = [];
		$achats_magazines_tot = [];
		$achats_magazines_current = [];

		foreach($resultat_achats as $i=>$achat) {
			$cpt = (int) $achat['cpt'];

			if (!array_key_exists($achat['Publicationcode'], $achats_magazines_current)) {
				$achats_magazines_current[$achat['Publicationcode']] = $cpt;
			}
			else {
				$achats_magazines_current[$achat['Publicationcode']] += $cpt;
			}

			if (!is_null($achat['Mois'])) {
				if (!array_key_exists($achat['Publicationcode'], $achats_magazines_nouv)) {
					$achats_magazines_nouv[$achat['Publicationcode']] = [];
					$achats_magazines_tot [$achat['Publicationcode']] = [];
				}
				$achats_magazines_nouv[$achat['Publicationcode']][$achat['Mois']]
					= $cpt;

				$achats_magazines_tot[$achat['Publicationcode']][$achat['Mois']]
					= $achats_magazines_current[$achat['Publicationcode']];

				if (is_null($premier_achat)) {
					$premier_achat = $achat;
				}
			}
		}

		$publication_codes = array_map(function($achat) {
			return $achat['Publicationcode'];
		}, $resultat_achats);

		$noms_complets_pays = Inducks::get_noms_complets_pays($publication_codes);
		$noms_complets_magazines = Inducks::get_noms_complets_magazines($publication_codes);

		return [
			'labels_pays_longs' => $noms_complets_pays,
			'labels_magazines_longs' => $noms_complets_magazines,
			'datasets' => [
				'nouv' => $achats_magazines_nouv,
				'tot' => $achats_magazines_nouv
            ],
			'premier_achat' => $premier_achat,
			'title' => ACHATS
        ];
	}

	static function getAuthorStoriesData() {
		$title = POSSESSION_HISTOIRES_AUTEURS;
		$legend = [HISTOIRES_POSSEDEES, HISTOIRES_NON_POSSEDEES];

        $resultat_stats_auteurs = DmClient::get_service_results_for_dm('GET', '/collection/stats/watchedauthorsstorycount');

		$possedees = [
			'label' => HISTOIRES_POSSEDEES,
			'backgroundColor' => '#FF8000',
			'data' => []
		];
		$possedees_pct = $possedees;

		$manquantes = [
			'label' => HISTOIRES_NON_POSSEDEES,
			'backgroundColor' => '#04B404',
			'data' => []
		];
		$manquantes_pct = $manquantes;

		$labels = [];

		foreach($resultat_stats_auteurs as $stat_utilisateur_auteur) {
			$auteur = $stat_utilisateur_auteur['fullname'];
			$total_auteur = $stat_utilisateur_auteur['storycount'];
			$possedees_auteur = $total_auteur - (int)$stat_utilisateur_auteur['missingstorycount'];
			$possedees_auteur_pct = round(100*($possedees_auteur/$total_auteur));

			$possedees['data'][]  = $possedees_auteur;
			$manquantes['data'][] = $total_auteur - $possedees_auteur ;

			$possedees_pct['data'][] = $possedees_auteur_pct;
			$manquantes_pct['data'][] = 100 - $possedees_auteur_pct;

			$labels[]=$auteur;
		}


		return [
			'datasets' => [
				'possedees' => $possedees, 'manquantes' => $manquantes,
				'possedees_pct' => $possedees_pct, 'manquantes_pct' => $manquantes_pct
			],
			'legend' => $legend,
			'labels' => $labels,
			'title' => $title,
		];
	}

	static function showSuggestedPublications($pays) {

        $suggestions = DmClient::get_service_results_for_dm('GET', '/collection/stats/suggestedissues', is_null($pays) ? [] : [$pays]);

		if (!isset($suggestions->issues) || count(get_object_vars($suggestions->issues)) === 0) {
			?><br /><?=AUCUNE_SUGGESTION?><?php
		}
		else {
			$minScore = $suggestions->minScore;
			$maxScore = $suggestions->maxScore;

			foreach($suggestions->issues as $issuecode => $issue) {
			    $publicationcode = $issue->publicationcode;
                $country = explode('/', $publicationcode)[0];
                $issuenumber = $issue->issuenumber;
			    $importance = $issue->score === $maxScore ? 1 : ($issue->score === $minScore ? 3 : 2);
				?>
				<div>
					<span class="numero top<?=$importance?>"><?php
					Affichage::afficher_texte_numero(
                        $country,
                        $suggestions->publicationTitles->$publicationcode,
                        $issuenumber
					);
					?>&nbsp;</span><?=NUMERO_CONTIENT?>
				</div>
				<ul class="liste_histoires"><?php
				foreach($issue->stories as $author => $storiesOfAuthor) {
					?><li>
						<div>
							<?=implode(' ', [count($storiesOfAuthor), count($storiesOfAuthor) === 1 ? HISTOIRE_INEDITE : HISTOIRES_INEDITES, DE, $suggestions->authors->$author])?>
						</div>
						<ul class="liste_histoires">
							<?php foreach($storiesOfAuthor as $storyCode) {
								?><li>
									<?php Affichage::afficher_texte_histoire($storyCode, @$suggestions->storyDetails->$storyCode->title, @$suggestions->storyDetails->$storyCode->storycomment);
								?></li>
								<?php
							}?>
						</ul>
					</li><?php
				}
				?></ul><?php
			}
		}
	}
}

Stats::$id_user=$_SESSION['id_user'];


if (isset($_POST['graph'])) {

	header('Content-Type: application/json');
	
	if (isset($_POST['publications'])) {
		echo json_encode(Stats::getPublicationData());
	}
	else if (isset($_POST['conditions'])) {
		echo json_encode(Stats::getConditionData());
	}
	else if (isset($_POST['achats'])) {
		echo json_encode(Stats::getPurchaseHistory());
	}
	else if (isset($_POST['auteurs'])) {
		echo json_encode(Stats::getAuthorStoriesData());
	}
	else if (isset($_POST['possessions'])) {
		if (isset($_POST['init_chargement'])) {
			$l=DM_Core::$d->toList(Stats::$id_user);
			echo json_encode(array_keys($l->collection));
		}
		else if (isset($_POST['element'])) {
			echo json_encode(Stats::getPossessionsData($_POST['element']));
		}
		else if (isset($_POST['fin'])) {
			echo json_encode(Stats::getPossessionsData());
		}
	}
}