<?php
@session_start();
$id_user=isset($_POST['id_user']) ? $_POST['id_user'] : (isset($_GET['id_user']) ? $_GET['id_user'] : null);
require_once ('Database.class.php');
require_once ('Util.class.php');
$debut = microtime(true);

$notations_tous_users = array();

$auteurs = array();
$requete_auteurs = 'SELECT DISTINCT NomAuteurAbrege FROM auteurs_pseudos '
				   .'WHERE DateStat =\'0000-00-00\'';
$resultat_auteurs = DM_Core::$d->requete_select($requete_auteurs);
foreach ($resultat_auteurs as $auteur)
	array_push($auteurs, $auteur['NomAuteurAbrege']);

if (!is_null($id_user))
	$l = DM_Core::$d->toList($id_user);
$liste_magazines=array();
foreach ($auteurs as $auteur) {
	if (empty($auteur))
		continue;
	
	$users=array();
	
	$requete_users = 'SELECT auteurs_pseudos.ID_User, users.RecommandationsListeMags FROM auteurs_pseudos INNER JOIN users ON auteurs_pseudos.ID_User=users.ID '
				   . 'WHERE auteurs_pseudos.NomAuteurAbrege = \'' . $auteur . '\' AND auteurs_pseudos.DateStat = \'0000-00-00\' ';
	if (!is_null($id_user))
		$requete_users.='AND ID_User=' . $id_user;
	$resultat_users = DM_Core::$d->requete_select($requete_users);
	foreach ($resultat_users as $user) {
		if ($user['RecommandationsListeMags'] == 1) {
			$l = DM_Core::$d->toList($user['ID_User']);
			$liste_magazines = $l->liste_magazines();
		}
		else
			$liste_magazines = array('vide' => 'vide');
		$users[ $user['ID_User'] ]= $liste_magazines;
	}
	
	if (count($users) != 0) {
		$requete='SELECT fullname FROM inducks_person WHERE personcode LIKE \''.$auteur.'\'';
		$requete_resultat=Inducks::requete_select($requete);
		$nom_auteur=$requete_resultat[0]['fullname'];
	
	}

	foreach ($users as $id_user => $liste_magazines) {
		$accepte_magazines_possedes_seulement = ! array_key_exists('vide',$liste_magazines);
		if (!array_key_exists($id_user, $notations_tous_users))
			$notations_tous_users[$id_user] = array();
		$notations_magazines = array();
		$requete_notation = 'SELECT Notation FROM auteurs_pseudos WHERE '
						  . 'NomAuteurAbrege = \'' . $auteur . '\' AND ID_user=' . $id_user . ' AND DateStat = \'0000-00-00\'';
		$resultat_notation = DM_Core::$d->requete_select($requete_notation);
		$notation_auteur = $resultat_notation[0]['Notation'] - 5;
		$l = DM_Core::$d->toList($id_user);

		$total_codes = 0;
		$possedes = 0;
		$publie_france_non_possede = 0;
		
		$requete_publications_histoire='SELECT DISTINCT sv.storyversioncode, sv.storycode, i.publicationcode, i.issuenumber
									    FROM inducks_storyversion sv
									    INNER JOIN inducks_entry e ON sv.storyversioncode = e.storyversioncode
									    INNER JOIN inducks_issue i ON e.issuecode = i.issuecode
									    WHERE sv.what=\'s\' AND sv.kind=\'n\' AND (sv.plotsummary LIKE \'%,'.$auteur.',%\' OR sv.writsummary LIKE \'%,'.$auteur.',%\' OR sv.artsummary LIKE \'%,'.$auteur.',%\' OR sv.inksummary LIKE \'%,'.$auteur.',%\')
									    ORDER BY sv.storycode';
		$resultat_requete_publications_histoire=Inducks::requete_select($requete_publications_histoire);

		$publie_france_non_possede=0;
		$publie_etranger_non_possede=0;
		$dernier_storycode = '';
		$est_possede=false;
		$est_publie_en_france=false;
		foreach($resultat_requete_publications_histoire as $publication) {
			$storycode=$publication['storycode'];
			if ($dernier_storycode == $storycode) {
				if ($est_possede)
					continue;
			}
			else { // Nouvelle histoire
				if (!empty($dernier_storycode)) { // Analyse de l'histoire précédente
					if ($est_possede)
						$possedes++;
					else {
						if ($est_publie_en_france)
							$publie_france_non_possede++;
						else
							$publie_etranger_non_possede++;
						
					 }
				}
				$est_publie_en_france=false;
				$est_possede=false;
			}
			$dernier_storycode = $storycode;
			
			list($pays,$magazine)=explode('/',$publication['publicationcode']);
			$numero=$publication['issuenumber'];
			if ($pays=='fr') {
				$est_publie_en_france=true;
			}

			if ($l->est_possede($pays,$magazine,$numero)) {
				$est_possede=true;
			}
			else {
				if (!$accepte_magazines_possedes_seulement|| array_key_exists($pays.'/'.$magazine,$liste_magazines)) {
					if (!array_key_exists($pays . '/' . $magazine . ' ' . $numero, $notations_magazines))
						$notations_magazines[$pays . '/' . $magazine . ' ' . $numero] = array('Score' => 0, 'Auteurs' => array($auteur=>0));
					$notations_magazines[$pays . '/' . $magazine . ' ' . $numero]['Score']+=$notation_auteur;
					$notations_magazines[$pays . '/' . $magazine . ' ' . $numero]['Auteurs'][$auteur]++;
				}
			}
		}
		$total_codes=$possedes+$publie_france_non_possede+$publie_etranger_non_possede;
		
		echo $possedes . ' poss&eacute;d&eacute;s sur ' . $total_codes . ' (' . $publie_france_non_possede . ' publi&eacute;s en France mais non poss&eacute;d&eacute;s)<br />';
		
		date_default_timezone_set('Europe/Paris');
		$requete_suppr_stats_existe = 'DELETE FROM auteurs_pseudos '
									. 'WHERE NomAuteurAbrege LIKE \'' . $auteur . '\' AND ID_User=' . $id_user . ' AND DateStat LIKE \'' . date('Y-m-d') . '\'';
		DM_Core::$d->requete($requete_suppr_stats_existe);
		$requete_stats = 'INSERT INTO auteurs_pseudos (NomAuteur, NomAuteurAbrege, ID_User, NbNonPossedesFrance, NbNonPossedesEtranger, NbPossedes, DateStat) '
					   . 'VALUES (\'' . $nom_auteur . '\',\'' . $auteur . '\',' . $id_user . ',' . $publie_france_non_possede . ',' . $publie_etranger_non_possede . ',' . $possedes . ',\'' . date('Y-m-d') . '\')';
		DM_Core::$d->requete($requete_stats);

		foreach ($notations_magazines as $numero => $score_auteurs) {
			$score_magazine = $score_auteurs['Score'];
			$auteurs = $score_auteurs['Auteurs'];
			if (!array_key_exists($numero, $notations_tous_users[$id_user]))
				$notations_tous_users[$id_user][$numero] = array('Numero' => $numero, 'Score' => 0, 'Auteurs' => array());
			$notations_tous_users[$id_user][$numero]['Score']+=$score_magazine;
			foreach ($auteurs as $auteur => $nb_histoires_auteur)
				$notations_tous_users[$id_user][$numero]['Auteurs'][$auteur] = $nb_histoires_auteur;
		}

		//echo '<pre>';print_r($notations_magazines);echo '</pre>';for ($k=0;$k<50;$k++) echo "\n";
	}
}
$notations_user2 = array();
foreach ($notations_tous_users as $user => $notations_user) {
	usort($notations_user, 'tri_notations');
	$notations_user2[$user] = $notations_user;
	$requete_supprime_recommandation = 'DELETE FROM numeros_recommandes WHERE ID_Utilisateur=' . $id_user;
	DM_Core::$d->requete($requete_supprime_recommandation);
	for ($i = count($notations_user2[$user]) - 20; $i < count($notations_user2[$user]); $i++) {
		list($pays, $magazine_numero) = explode('/', $notations_user2[$user][$i]['Numero']);
		$magazine_numero=explode(' ',$magazine_numero);
		$magazine = $magazine_numero[0];
		$numero = implode(' ',array_slice($magazine_numero,1,count($magazine_numero)-1));
		$notation = $notations_user2[$user][$i]['Score'];
		$auteurs_et_nb_histoires = array();
		if (isset($notations_user2[$user][$i]['Auteurs'])) {
			foreach ($notations_user2[$user][$i]['Auteurs'] as $auteur => $nb_histoires) {
				$auteurs_et_nb_histoires[]= $auteur.'='.$nb_histoires;
			}
		}
		$requete_ajout_recommandation = 'INSERT INTO numeros_recommandes(Pays,Magazine,Numero,Notation,ID_Utilisateur,Texte) '
			. 'VALUES (\'' . $pays . '\',\'' . $magazine . '\',\'' . $numero . '\',' . $notation . ',' . $user . ',\'' . implode($auteurs_et_nb_histoires,',') . '\')';
		echo $requete_ajout_recommandation.'<br />';
		DM_Core::$d->requete($requete_ajout_recommandation);
	}
}
echo '<pre>';
//print_r($notations_user2);
echo '</pre>';

$fin = microtime(true);
echo "\nTemps total : " . ($fin - $debut) . ' ms';


function tri_notations($numero1, $numero2) {
	if ($numero1['Score'] == $numero2['Score'])
		return 0;
	return ($numero1['Score'] < $numero2['Score']) ? -1 : 1;
}