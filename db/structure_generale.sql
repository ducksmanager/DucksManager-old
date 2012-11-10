-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.5.24-log - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2012-11-10 10:07:50
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping database structure for db301759616
CREATE DATABASE IF NOT EXISTS `db301759616` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_german2_ci */;
USE `db301759616`;


-- Dumping structure for table db301759616.achats
CREATE TABLE IF NOT EXISTS `achats` (
  `ID_Acquisition` int(11) NOT NULL AUTO_INCREMENT,
  `ID_User` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Style_couleur` varchar(9) DEFAULT NULL,
  `Style_soulignement` enum('Aucun','Simple','Double','Triple','Pointillé','Zig-zag','Double zig-zag','Ondulé','Double ondulé') DEFAULT NULL,
  `Style_entourage` enum('Aucun','Simple','Double','Pointillé','Rectangulaire') DEFAULT NULL,
  `Style_marquage` enum('Aucun','Simple','Double','Triple','Pointillé','Zig-zag','Double zig-zag','Ondulé','Double ondulé') DEFAULT NULL,
  `Description` varchar(100) NOT NULL,
  UNIQUE KEY `Acquisition,Date est unique` (`ID_Acquisition`,`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.auteurs
CREATE TABLE IF NOT EXISTS `auteurs` (
  `NomAuteurAbrege` varchar(20) NOT NULL,
  `NomAuteurComplet` varchar(40) NOT NULL,
  KEY `Cle` (`NomAuteurAbrege`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.auteurs_pseudos
CREATE TABLE IF NOT EXISTS `auteurs_pseudos` (
  `NomAuteur` varchar(50) CHARACTER SET utf8 NOT NULL,
  `NomAuteurAbrege` varchar(30) CHARACTER SET latin1 NOT NULL,
  `ID_user` int(11) NOT NULL,
  `NbNonPossedesFrance` int(11) NOT NULL DEFAULT '0',
  `NbNonPossedesEtranger` int(11) NOT NULL DEFAULT '0',
  `NbPossedes` int(11) NOT NULL,
  `DateStat` date NOT NULL DEFAULT '0000-00-00',
  `Notation` tinyint(3) DEFAULT '-1',
  UNIQUE KEY `NomAuteur` (`NomAuteurAbrege`,`ID_user`,`DateStat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.bibliotheque_contributeurs
CREATE TABLE IF NOT EXISTS `bibliotheque_contributeurs` (
  `Nom` varchar(30) COLLATE latin1_german2_ci DEFAULT NULL,
  `Texte` text COLLATE latin1_german2_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.bibliotheque_options
CREATE TABLE IF NOT EXISTS `bibliotheque_options` (
  `Pays` varchar(3) COLLATE latin1_german2_ci DEFAULT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci DEFAULT NULL,
  `Numero` varchar(8) COLLATE latin1_german2_ci DEFAULT NULL,
  `CouleurR` tinyint(8) unsigned DEFAULT '0',
  `CouleurG` tinyint(8) unsigned DEFAULT '0',
  `CouleurB` tinyint(8) unsigned DEFAULT '0',
  `Autre` text COLLATE latin1_german2_ci,
  UNIQUE KEY `Index 1` (`Pays`,`Magazine`,`Numero`,`Autre`(100))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.bibliotheque_ordre_magazines
CREATE TABLE IF NOT EXISTS `bibliotheque_ordre_magazines` (
  `Pays` varchar(3) COLLATE latin1_german2_ci DEFAULT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci DEFAULT NULL,
  `Ordre` int(3) DEFAULT NULL,
  `ID_Utilisateur` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.bouquineries
CREATE TABLE IF NOT EXISTS `bouquineries` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Nom` varchar(25) NOT NULL,
  `Adresse` text NOT NULL,
  `CodePostal` int(11) NOT NULL,
  `Ville` varchar(20) NOT NULL,
  `Pays` varchar(20) NOT NULL DEFAULT 'France',
  `Commentaire` text NOT NULL,
  `ID_Utilisateur` int(11) DEFAULT NULL,
  `CoordX` float NOT NULL DEFAULT '0',
  `CoordY` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.demo
CREATE TABLE IF NOT EXISTS `demo` (
  `DateDernierInit` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.emails_ventes
CREATE TABLE IF NOT EXISTS `emails_ventes` (
  `username_achat` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `username_vente` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`username_achat`,`username_vente`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.numeros
CREATE TABLE IF NOT EXISTS `numeros` (
  `Pays` varchar(3) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(8) COLLATE utf8_bin NOT NULL,
  `Etat` enum('mauvais','moyen','bon','indefini') COLLATE utf8_bin NOT NULL,
  `ID_Acquisition` int(11) NOT NULL DEFAULT '-1',
  `AV` tinyint(1) NOT NULL,
  `ID_Utilisateur` int(11) NOT NULL,
  `DateAjout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `Pays` (`Pays`,`Magazine`,`Numero`,`ID_Utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.numeros_recommandes
CREATE TABLE IF NOT EXISTS `numeros_recommandes` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `Notation` tinyint(3) NOT NULL,
  `ID_Utilisateur` int(11) NOT NULL,
  `Texte` text COLLATE latin1_german2_ci NOT NULL,
  `Storycodes` text COLLATE latin1_german2_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.parametres_listes
CREATE TABLE IF NOT EXISTS `parametres_listes` (
  `ID_Utilisateur` int(10) DEFAULT NULL,
  `Pays` varchar(3) COLLATE latin1_german2_ci DEFAULT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci DEFAULT NULL,
  `Type_Liste` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `Position_Liste` int(4) DEFAULT NULL,
  `Parametre` varchar(30) COLLATE latin1_german2_ci DEFAULT NULL,
  `Valeur` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  KEY `Index 1` (`ID_Utilisateur`,`Pays`,`Magazine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.pays
CREATE TABLE IF NOT EXISTS `pays` (
  `NomAbrege` varchar(10) COLLATE latin1_german2_ci DEFAULT NULL,
  `NomComplet` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `L10n` varchar(5) COLLATE latin1_german2_ci DEFAULT 'fr',
  UNIQUE KEY `Cle` (`NomAbrege`,`NomComplet`,`L10n`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.users
CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(25) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(40) NOT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AccepterPartage` tinyint(1) NOT NULL,
  `DateInscription` date NOT NULL DEFAULT '0000-00-00',
  `Email` varchar(50) NOT NULL DEFAULT '',
  `RecommandationsListeMags` tinyint(1) NOT NULL DEFAULT '1',
  `BetaUser` tinyint(3) unsigned NOT NULL,
  `AfficherVideo` tinyint(1) NOT NULL DEFAULT '1',
  `Bibliotheque_Texture1` varchar(20) NOT NULL DEFAULT 'bois',
  `Bibliotheque_Sous_Texture1` varchar(50) NOT NULL DEFAULT 'HONDURAS MAHOGANY',
  `Bibliotheque_Texture2` varchar(20) NOT NULL DEFAULT 'bois',
  `Bibliotheque_Sous_Texture2` varchar(50) NOT NULL DEFAULT 'KNOTTY PINE',
  `Bibliotheque_Grossissement` double unsigned NOT NULL DEFAULT '1.5',
  PRIMARY KEY (`ID`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.users_preferences_impression
CREATE TABLE IF NOT EXISTS `users_preferences_impression` (
  `ID_user` int(10) NOT NULL,
  `OrdreAffichage` tinyint(4) NOT NULL,
  `Pays` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(7) COLLATE latin1_german2_ci NOT NULL,
  `TypeListe` varchar(20) COLLATE latin1_german2_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
