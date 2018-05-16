<?php
require_once 'DucksManager_Core.class.php';
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
	
	if (isset($_GET['debug'])) {
        echo 'Id user demo : ' . $id_user_demo;
    }
	
	$requete_reset_user="
	DELETE FROM numeros WHERE ID_Utilisateur=".$id_user_demo.";
	DELETE FROM achats WHERE ID_User=".$id_user_demo.";
	DELETE FROM auteurs_pseudos WHERE ID_user=".$id_user_demo.";
	
	UPDATE `users` SET `Bibliotheque_Texture1` = 'bois', 
					   `Bibliotheque_Sous_Texture1` = 'HONDURAS MAHOGANY', 
					   `Bibliotheque_Texture2` = 'bois', 
					   `Bibliotheque_Sous_Texture2` = 'KNOTTY PINE'
				  WHERE ID_Utilisateur=".$id_user_demo.";
	
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
	
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1000, ".$id_user_demo.", '2011-10-15', NULL, NULL, NULL, NULL, 'Bouquinerie Bordeaux');
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1001, ".$id_user_demo.", '2011-11-01', NULL, NULL, NULL, NULL, 'Bouquinerie La Rochelle');
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1002, ".$id_user_demo.", '2011-10-25', NULL, NULL, NULL, NULL, 'Bouquinerie Madrid');
	REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Style_couleur`, `Style_soulignement`, `Style_entourage`, `Style_marquage`, `Description`) VALUES (1003, ".$id_user_demo.", '2011-12-08', NULL, NULL, NULL, NULL, 'Virgin Bordeaux');
	
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Carl Barks', 'CB', ".$id_user_demo.", 516, 140, 153, '2011-12-27', -1);
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Don Rosa', 'DR', ".$id_user_demo.", 90, 24, 2, '2011-12-27', -1);
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Barks,Carl', 'CB', ".$id_user_demo.", 0, 0, 0, '0000-00-00', 6);
	INSERT INTO `auteurs_pseudos` (`NomAuteur`, `NomAuteurAbrege`, `ID_user`, `NbNonPossedesFrance`, `NbNonPossedesEtranger`, `NbPossedes`, `DateStat`, `Notation`) VALUES ('Rosa, Don', 'DR', ".$id_user_demo.", 0, 0, 0, '0000-00-00', 8);";
	
	$requete_reset_user=str_replace("\n",'', array_filter(explode(';', $requete_reset_user)));
	
	foreach($requete_reset_user as $requete) {
		DM_Core::$d->requete($requete);
		if (isset($_GET['debug'])) {
            echo $requete . '<br />';
        }
	}
}