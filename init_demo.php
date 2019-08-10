<?php
require_once 'DucksManager_Core.class.php';
date_default_timezone_set('Europe/Paris');


$resultat_dernier_init_recent=DM_Core::$d->requete('SELECT DateDernierInit FROM demo');
$derniere_date=strtotime($resultat_dernier_init_recent[0]['DateDernierInit']);
$dernier_init_est_recent=(time() - $derniere_date) / 3600 < 1;

if (false) {
    $str_time=strftime('%Y-%m-%d %H:00:00',time());
    $requete_update_date_init='UPDATE demo SET DateDernierInit=\''.$str_time.'\'';
    DM_Core::$d->requete($requete_update_date_init);
    
    $resultat_id_user_demo=DM_Core::$d->requete('SELECT ID FROM users WHERE username=\'demo\'');
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
                  WHERE ID=".$id_user_demo.";
    
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

    REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Description`) VALUES (1000, ".$id_user_demo.", '2011-10-15', 'Bouquinerie Bordeaux');
    REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Description`) VALUES (1001, ".$id_user_demo.", '2011-11-01', 'Bouquinerie La Rochelle');
    REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Description`) VALUES (1002, ".$id_user_demo.", '2011-10-25', 'Bouquinerie Madrid');
    REPLACE INTO `achats` (`ID_Acquisition`, `ID_User`, `Date`, `Description`) VALUES (1003, ".$id_user_demo.", '2011-12-08', 'Virgin Bordeaux');
    
    INSERT INTO `auteurs_pseudos` (`NomAuteurAbrege`, `ID_user`, `Notation`) VALUES ('CB', ".$id_user_demo.", 6);
    INSERT INTO `auteurs_pseudos` (`NomAuteurAbrege`, `ID_user`, `Notation`) VALUES ('DR', ".$id_user_demo.", 8);";
    
    $requete_reset_user=str_replace("\n",'', array_filter(explode(';', $requete_reset_user)));
    
    foreach($requete_reset_user as $requete) {
        DM_Core::$d->requete($requete);
        if (isset($_GET['debug'])) {
            echo $requete . '<br />';
        }
    }
}
