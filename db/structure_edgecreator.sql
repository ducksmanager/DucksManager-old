-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.5.24-log - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2012-11-10 10:10:53
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping database structure for db301759616
CREATE DATABASE IF NOT EXISTS `db301759616` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_german2_ci */;
USE `db301759616`;


-- Dumping structure for table db301759616.edgecreator_droits
CREATE TABLE IF NOT EXISTS `edgecreator_droits` (
  `username` varchar(25) COLLATE latin1_german2_ci NOT NULL,
  `privilege` enum('Admin','Edition','Affichage') COLLATE latin1_german2_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.edgecreator_intervalles
CREATE TABLE IF NOT EXISTS `edgecreator_intervalles` (
  `ID_Valeur` int(10) NOT NULL,
  `Numero_debut` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `Numero_fin` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `username` varchar(25) COLLATE latin1_german2_ci NOT NULL,
  KEY `Index 1` (`ID_Valeur`,`Numero_debut`,`Numero_fin`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.edgecreator_modeles2
CREATE TABLE IF NOT EXISTS `edgecreator_modeles2` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Ordre` float NOT NULL,
  `Nom_fonction` varchar(30) COLLATE latin1_german2_ci NOT NULL,
  `Option_nom` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for view db301759616.edgecreator_modeles_vue
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `edgecreator_modeles_vue` (
	`Pays` VARCHAR(3) NOT NULL COLLATE 'latin1_german2_ci',
	`Magazine` VARCHAR(6) NOT NULL COLLATE 'latin1_german2_ci',
	`Ordre` FLOAT NOT NULL,
	`Nom_fonction` VARCHAR(30) NOT NULL COLLATE 'latin1_german2_ci',
	`Option_nom` VARCHAR(20) NULL DEFAULT NULL COLLATE 'latin1_german2_ci',
	`ID` INT(11) NOT NULL DEFAULT '0',
	`ID_Valeur` INT(10) NOT NULL DEFAULT '0',
	`Option_valeur` VARCHAR(200) NULL DEFAULT NULL COLLATE 'latin1_german2_ci',
	`Numero_debut` VARCHAR(10) NOT NULL COLLATE 'latin1_german2_ci',
	`Numero_fin` VARCHAR(10) NOT NULL COLLATE 'latin1_german2_ci',
	`username` VARCHAR(25) NOT NULL COLLATE 'latin1_german2_ci'
) ENGINE=MyISAM;


-- Dumping structure for table db301759616.edgecreator_valeurs
CREATE TABLE IF NOT EXISTS `edgecreator_valeurs` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `ID_Option` int(10) DEFAULT NULL,
  `Option_valeur` varchar(200) COLLATE latin1_german2_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.images_myfonts
CREATE TABLE IF NOT EXISTS `images_myfonts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Font` varchar(150) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `Color` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `ColorBG` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `Width` varchar(7) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `Texte` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `Precision_` varchar(5) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.magazines
CREATE TABLE IF NOT EXISTS `magazines` (
  `PaysAbrege` varchar(4) CHARACTER SET latin1 NOT NULL,
  `NomAbrege` varchar(7) CHARACTER SET latin1 NOT NULL,
  `NomComplet` varchar(70) COLLATE utf8_bin NOT NULL,
  `RedirigeDepuis` varchar(7) COLLATE utf8_bin DEFAULT NULL,
  `NeParaitPlus` tinyint(1) DEFAULT NULL,
  KEY `Index 1` (`PaysAbrege`,`NomAbrege`,`RedirigeDepuis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.tranches_doublons
CREATE TABLE IF NOT EXISTS `tranches_doublons` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(8) COLLATE latin1_german2_ci NOT NULL,
  `NumeroReference` varchar(8) COLLATE latin1_german2_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for table db301759616.tranches_pretes
CREATE TABLE IF NOT EXISTS `tranches_pretes` (
  `publicationcode` varchar(12) COLLATE latin1_german2_ci DEFAULT NULL,
  `issuenumber` varchar(10) COLLATE latin1_german2_ci DEFAULT NULL,
  `photographes` text COLLATE latin1_german2_ci,
  `createurs` text COLLATE latin1_german2_ci,
  `dateajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `Index 1` (`publicationcode`,`issuenumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- Data exporting was unselected.


-- Dumping structure for view db301759616.edgecreator_modeles_vue
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `edgecreator_modeles_vue`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` VIEW `edgecreator_modeles_vue` AS select `edgecreator_modeles2`.`Pays` AS `Pays`,`edgecreator_modeles2`.`Magazine` AS `Magazine`,`edgecreator_modeles2`.`Ordre` AS `Ordre`,`edgecreator_modeles2`.`Nom_fonction` AS `Nom_fonction`,`edgecreator_modeles2`.`Option_nom` AS `Option_nom`,`edgecreator_modeles2`.`ID` AS `ID`,`edgecreator_valeurs`.`ID` AS `ID_Valeur`,`edgecreator_valeurs`.`Option_valeur` AS `Option_valeur`,`edgecreator_intervalles`.`Numero_debut` AS `Numero_debut`,`edgecreator_intervalles`.`Numero_fin` AS `Numero_fin`,`edgecreator_intervalles`.`username` AS `username` from ((`edgecreator_modeles2` join `edgecreator_valeurs` on((`edgecreator_modeles2`.`ID` = `edgecreator_valeurs`.`ID_Option`))) join `edgecreator_intervalles` on((`edgecreator_valeurs`.`ID` = `edgecreator_intervalles`.`ID_Valeur`))) order by `edgecreator_modeles2`.`Pays`,`edgecreator_modeles2`.`Magazine`,`edgecreator_modeles2`.`Ordre`,`edgecreator_modeles2`.`Option_nom`,`edgecreator_intervalles`.`Numero_debut` ;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
