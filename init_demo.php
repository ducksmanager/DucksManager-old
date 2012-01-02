<?php
require_once('DucksManager_Core.class.php');
date_default_timezone_set('Europe/Paris');


$resultat_dernier_init_recent=DM_Core::$d->requete_select('SELECT DateDernierInit FROM demo');
$derniere_date=strtotime($resultat_dernier_init_recent[0]['DateDernierInit']);
$dernier_init_est_recent=(time() - $derniere_date) / 3600 < 1;

if (!$dernier_init_est_recent) {
	$str_time=strftime('%Y-%m-%d %H:00:00',time());
	$requete_update_date_init='UPDATE demo SET DateDernierInit=\''.$str_time.'\'';
	DM_Core::$d->requete($requete_update_date_init);
	
	$resultat_id_user_demo=DM_Core::$d->requete_select('SELECT ID FROM users WHERE username=\'demo\'');
	$id_user_demo=$resultat_id_user_demo[0]['ID'];
	
	if (isset($_GET['debug']))
		echo 'Id user demo : '.$id_user_demo;
	
	$requete_reset_user="
	DELETE FROM numeros WHERE ID_Utilisateur=".$id_user_demo.";
	DELETE FROM achats WHERE ID_User=".$id_user_demo.";
	DELETE FROM auteurs_pseudos WHERE ID_user=".$id_user_demo.";
	DELETE FROM numeros_recommandes WHERE ID_Utilisateur=".$id_user_demo.";
	DELETE FROM parametres_listes WHERE ID_Utilisateur=".$id_user_demo.";
	
	REPLACE INTO `users` (`username`, `password`, `ID`, `AccepterPartage`, `DateInscription`, `Email`, `RecommandationsListeMags`, `BetaUser`, `AfficherVideo`, `Bibliotheque_Texture1`, `Bibliotheque_Sous_Texture1`, `Bibliotheque_Texture2`, `Bibliotheque_Sous_Texture2`, `Bibliotheque_Grossissement`) VALUES ('demo', 'demodemo', ".$id_user_demo.", 0, '2011-12-15', 'demo@demo.demo', 1, 0, 0, 'bois', 'HONDURAS MAHOGANY', 'bois', 'KNOTTY PINE', 1.5);
	
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'CB', 'P 88', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'DDD', '3', 'bon', 1003, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'DDD', '2', 'bon', 1003, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'DDD', '1', 'bon', 1003, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MCO', '4', 'bon', 1000, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MCO', '3', 'bon', 1000, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MCO', '2', 'bon', 1000, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MCO', '1', 'bon', 1000, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '190', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '191', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '192', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '193', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '213', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '214', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '215', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '216', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '217', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '309', 'moyen', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '310', 'moyen', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '313', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '314', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '317', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '318', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '319', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '320', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '321', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'MP', '322', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'PM', '381', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'PM', '382', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'PM', '383', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'PM', '384', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'PM', '386', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'PM', '387', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('us', 'WDC', '375', 'bon', 1001, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('es', 'BCB', '1', 'bon', 1002, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'ALPM', 'A E', 'bon', -2, 0, ".$id_user_demo.");
	INSERT INTO `numeros` (`Pays`, `Magazine`, `Numero`, `Etat`, `ID_Acquisition`, `AV`, `ID_Utilisateur`) VALUES ('fr', 'ALPM', 'A V', 'bon', -2, 0, ".$id_user_demo.");
	
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1000, ".$id_user_demo.", '2011-10-15', NULL, NULL, NULL, NULL, 'Bouquinerie Bordeaux');
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1001, ".$id_user_demo.", '2011-11-01', NULL, NULL, NULL, NULL, 'Bouquinerie La Rochelle');
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1002, ".$id_user_demo.", '2011-10-25', NULL, NULL, NULL, NULL, 'Bouquinerie Madrid');
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1003, ".$id_user_demo.", '2011-12-08', NULL, NULL, NULL, NULL, 'Virgin Bordeaux');
	
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Carl Barks', 'CB', ".$id_user_demo.", 516, 140, 153, '2011-12-27', -1);
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Don Rosa', 'DR', ".$id_user_demo.", 90, 24, 2, '2011-12-27', -1);
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Barks,Carl', 'CB', ".$id_user_demo.", 0, 0, 0, '0000-00-00', 6);
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Rosa, Don', 'DR', ".$id_user_demo.", 0, 0, 0, '0000-00-00', 8);
	
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 30', 48, ".$id_user_demo.", 'CB=21,DR=2', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('es', 'BCB', '3', 46, ".$id_user_demo.", 'CB=23', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'PM', '331', 45, ".$id_user_demo.", 'CB=21,DR=1', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 42', 42, ".$id_user_demo.", 'CB=15,DR=4', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('es', 'BCB', '2', 40, ".$id_user_demo.", 'CB=20', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 43', 40, ".$id_user_demo.", 'CB=17,DR=2', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 35', 40, ".$id_user_demo.", 'CB=20', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 41', 39, ".$id_user_demo.", 'CB=15,DR=3', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 28', 38, ".$id_user_demo.", 'CB=13,DR=4', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 34', 36, ".$id_user_demo.", 'CB=15,DR=2', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('es', 'BCB', '4', 34, ".$id_user_demo.", 'CB=17', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 39', 33, ".$id_user_demo.", 'CB=12,DR=3', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 38', 32, ".$id_user_demo.", 'CB=10,DR=4', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 25', 32, ".$id_user_demo.", 'CB=7,DR=6', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 29', 31, ".$id_user_demo.", 'CB=11,DR=3', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'PM', '337', 30, ".$id_user_demo.", 'CB=15', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 24', 29, ".$id_user_demo.", 'CB=10,DR=3', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'PM', '343', 28, ".$id_user_demo.", 'CB=14', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'ALPM', 'B 31', 55, ".$id_user_demo.", 'CB=26,DR=1', NULL);
	INSERT INTO `numeros_recommandes` (`Pays`, `Magazine`, `Numero`, `Notation`, `ID_Utilisateur`, `Texte`, `Storycodes`) VALUES ('fr', 'DDD', '4', 62, ".$id_user_demo.", 'CB=31', NULL);";
	
	
	$requete_reset_user=str_replace("\n",'',explode(';',$requete_reset_user));
	
	foreach($requete_reset_user as $requete) {
		DM_Core::$d->requete($requete);
		if (isset($_GET['debug']))
			echo $requete.'<br />';
	}
}


?>