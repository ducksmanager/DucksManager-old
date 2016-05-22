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
				$labels[]=Database::$etats[$etat_court][0];
				$colors[]= sprintf('#%06X', mt_rand(0, 0xFFFFFF));
			}
		}
		if ($autres!=0) {
			$data[]=$autres;
			$labels[]=AUTRES;
			$colors[]= sprintf('#%06X', mt_rand(0, 0xFFFFFF));
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
				$totaux['data'][] = intval($donnee->total)-$donnee->possede;
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
				}
				else {
					$retour['total'][$magazine]=0;
					$retour['possede'][$magazine]=0;
					$retour['possede_pct'][$magazine]=0;
				}
			}
			return $retour;
		}
	}
	
	static function getPurchaseHistory() {
		$id_user=static::$id_user;

		$requete_achats = "
			SELECT DATE_FORMAT(Date,'%Y-%m') AS Mois,CONCAT(Pays, '/', Magazine) AS Publicationcode, Count(Numero) AS cpt
			FROM numeros n
			  LEFT JOIN achats USING (ID_Acquisition)
			WHERE ID_Utilisateur=$id_user
			GROUP BY YEAR(Date), MONTH(Date), Pays,Magazine
			ORDER BY YEAR(Date), MONTH(Date)
		";
		$resultat_achats = DM_Core::$d->requete_select($requete_achats);

		$premier_achat = null;
		$achats_magazines = array();

		foreach($resultat_achats as $achat) {
			if (is_null($premier_achat) && !is_null($achat['Mois'])) {
				$premier_achat = $achat;
			}

			if (!array_key_exists($achat['Publicationcode'], $achats_magazines)) {
				$achats_magazines[$achat['Publicationcode']] = array();
			}
			$achats_magazines[$achat['Publicationcode']][$achat['Mois']] = (int) $achat['cpt'];
		}

		$publication_codes = array_map(function($achat) {
			return $achat['Publicationcode'];
		}, $resultat_achats);

		list($noms_complets_pays,$noms_complets_magazines)=Inducks::get_noms_complets($publication_codes);

		return array(
			'labels_pays_longs' => $noms_complets_pays,
			'labels_magazines_longs' => $noms_complets_magazines,
			'datasets' => array(
				'nouv' => $achats_magazines
			),
			'premier_achat' => $premier_achat,
			'title' => ACHATS
		);
	}

	static function getAuthorStoriesData() {
		$id_user=static::$id_user;

		$title = POSSESSION_HISTOIRES_AUTEURS;
		
		$legend = [HISTOIRES_POSSEDEES, HISTOIRES_NON_POSSEDEES];

		$requete_auteurs = "
			SELECT a_h.personcode, p.fullname, COUNT(a_h.storycode) AS cpt
			FROM dm_stats.auteurs_histoires a_h
			INNER JOIN coa.inducks_person p ON a_h.personcode = p.personcode
			GROUP BY a_h.personcode
		";

		$resultat_auteurs = Inducks::requete_select($requete_auteurs, 'dm_stats');
		$auteurs = [];

		foreach($resultat_auteurs as $auteur) {
			$auteurs[$auteur['personcode']] = ['fullname' => $auteur['fullname'], 'cpt' => $auteur['cpt']];
		}

		$requete_stats_auteurs = "
			SELECT u_h_m.personcode, COUNT(u_h_m.storycode) AS cpt
			FROM dm_stats.utilisateurs_histoires_manquantes u_h_m
			WHERE u_h_m.ID_User = $id_user
			GROUP BY u_h_m.personcode
		";

		$resultat_stats_auteurs = Inducks::requete_select($requete_stats_auteurs, 'dm_stats');

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
			$auteur = $stat_utilisateur_auteur['personcode'];
			$total_auteur = $auteurs[$auteur]['cpt'];
			$possedees_auteur = $total_auteur - (int)$stat_utilisateur_auteur['cpt'];
			$possedees_auteur_pct = round(100*($possedees_auteur/$total_auteur));

			$possedees['data'][]  = $possedees_auteur;
			$manquantes['data'][] = $total_auteur - $possedees_auteur ;

			$possedees_pct['data'][] = $possedees_auteur_pct;
			$manquantes_pct['data'][] = 100 - $possedees_auteur_pct;

			$labels[]=$auteurs[$auteur]['fullname'];
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

	static function showSuggestedPublications() {
		$id_user=static::$id_user;

		$requete_suggestions_publications = "
			SELECT 
				u_p_s.publicationcode, u_p_s.issuenumber, u_p_s.Score, p.fullname as author,
				GROUP_CONCAT(u_p_m.storycode SEPARATOR '---') AS storycodes,
				GROUP_CONCAT(s.title SEPARATOR '---') AS storytitles,
				GROUP_CONCAT(s.storycomment SEPARATOR '---') AS storycomments
			FROM dm_stats.utilisateurs_publications_suggerees u_p_s
			  INNER JOIN dm_stats.utilisateurs_publications_manquantes u_p_m
				ON u_p_s.ID_User = u_p_m.ID_User AND u_p_s.publicationcode = u_p_m.publicationcode AND u_p_s.issuenumber = u_p_m.issuenumber
			  INNER JOIN coa.inducks_person p
				ON u_p_m.personcode = p.personcode
			  INNER JOIN coa.inducks_story s 
			  	ON u_p_m.storycode = s.storycode
			WHERE u_p_s.ID_User=$id_user
			GROUP BY u_p_s.publicationcode, u_p_s.issuenumber, u_p_m.personcode
			ORDER BY u_p_s.Score DESC
			LIMIT 20
		";
		$resultat_suggestions_publications = Inducks::requete_select($requete_suggestions_publications, 'dm_stats');

		$min_score = $resultat_suggestions_publications[count($resultat_suggestions_publications) - 1]['Score'];
		$max_score = $resultat_suggestions_publications[0]['Score'];

		$publicationcodes = array_map(function($suggestion) {
			return $suggestion['publicationcode'];
		}, $resultat_suggestions_publications);

		$resultat_suggestions_publications_groupes = [];
		array_walk($resultat_suggestions_publications, function ($suggestion) use (&$resultat_suggestions_publications_groupes, $min_score, $max_score) {
			$issue = implode(' ', array($suggestion['publicationcode'], $suggestion['issuenumber']));
			if (!array_key_exists($issue, $resultat_suggestions_publications_groupes)) {
				$resultat_suggestions_publications_groupes[$issue] = [
					'publicationcode' => $suggestion['publicationcode'],
					'issuenumber' => $suggestion['issuenumber'],
					'Score' => $suggestion['Score'],
					'Importance' => $suggestion['Score'] === $max_score ? 1 : ($suggestion['Score'] === $min_score ? 3 : 2),
					'stories' => [],
				];
			}
			$resultat_suggestions_publications_groupes[$issue]['stories'][$suggestion['author']] = [
				'titles' => explode('---', $suggestion['storytitles']),
				'comments' => explode('---', $suggestion['storycomments']),
				'codes' => explode('---', $suggestion['storycodes'])
			];
		});

		list($liste_pays_complets,$liste_magazines_complets) = Inducks::get_noms_complets($publicationcodes);

		foreach($resultat_suggestions_publications_groupes as $suggestion) {
			list($pays,$magazine) = explode('/', $suggestion['publicationcode']);
			?>
			<div>
				<span class="numero top<?=$suggestion['Importance']?>"><?php
				Affichage::afficher_texte_numero(
					$pays,
					$liste_magazines_complets[$suggestion['publicationcode']],
					$suggestion['issuenumber']
				);
				?>&nbsp;</span><?=NUMERO_CONTIENT?>
			</div>
			<ul class="liste_histoires"><?php
			foreach($suggestion['stories'] as $author => $stories) {
				?><li>
					<div>
						<?=implode(' ', [count($stories['codes']), count($stories['codes']) === 1 ? HISTOIRES_INEDITES : HISTOIRES_INEDITES, DE, $author])?>
					</div>
					<ul class="liste_histoires">
						<?php foreach($stories['codes'] as $i_code => $code) {
							?><li>
								<?php Affichage::afficher_texte_histoire($code, $stories['titles'][$i_code], $stories['comments'][$i_code]);
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

Stats::$id_user=DM_Core::$d->user_to_id($_SESSION['user']);

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