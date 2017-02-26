-- MySQL dump 10.13  Distrib 5.5.41, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: db301759616
-- ------------------------------------------------------
-- Server version	5.5.41-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `achats`
--

DROP TABLE IF EXISTS `achats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achats` (
  `ID_Acquisition` int(11) NOT NULL AUTO_INCREMENT,
  `ID_User` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Style_couleur` varchar(9) DEFAULT NULL,
  `Style_soulignement` enum('Aucun','Simple','Double','Triple','Pointillé','Zig-zag','Double zig-zag','Ondulé','Double ondulé') DEFAULT NULL,
  `Style_entourage` enum('Aucun','Simple','Double','Pointillé','Rectangulaire') DEFAULT NULL,
  `Style_marquage` enum('Aucun','*','+','!') DEFAULT NULL,
  `Description` varchar(100) NOT NULL,
  UNIQUE KEY `Acquisition,Date est unique` (`ID_Acquisition`,`Date`)
) ENGINE=MyISAM AUTO_INCREMENT=1103 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auteurs_pseudos`
--

DROP TABLE IF EXISTS `auteurs_pseudos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auteurs_pseudos` (
  `NomAuteur` varchar(50) CHARACTER SET utf8 NOT NULL,
  `NomAuteurAbrege` varchar(30) CHARACTER SET latin1 NOT NULL,
  `ID_user` int(11) NOT NULL,
  `NbNonPossedesFrance` int(11) NOT NULL DEFAULT '0',
  `NbNonPossedesEtranger` int(11) NOT NULL DEFAULT '0',
  `NbPossedes` int(11) NOT NULL,
  `DateStat` date NOT NULL DEFAULT '0000-00-00',
  `Notation` tinyint(4) NOT NULL DEFAULT '-1',
  UNIQUE KEY `NomAuteur` (`NomAuteurAbrege`,`ID_user`,`DateStat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bibliotheque_acces_externes`
--

DROP TABLE IF EXISTS `bibliotheque_acces_externes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bibliotheque_acces_externes` (
  `ID_Utilisateur` int(11) NOT NULL,
  `Cle` varchar(16) NOT NULL,
  PRIMARY KEY (`ID_Utilisateur`,`Cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bibliotheque_contributeurs`
--

DROP TABLE IF EXISTS `bibliotheque_contributeurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bibliotheque_contributeurs` (
  `Nom` varchar(30) COLLATE latin1_german2_ci DEFAULT NULL,
  `Texte` text COLLATE latin1_german2_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bibliotheque_ordre_magazines`
--

DROP TABLE IF EXISTS `bibliotheque_ordre_magazines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bibliotheque_ordre_magazines` (
  `Pays` varchar(3) COLLATE latin1_german2_ci DEFAULT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci DEFAULT NULL,
  `Ordre` int(3) DEFAULT NULL,
  `ID_Utilisateur` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bouquineries`
--

DROP TABLE IF EXISTS `bouquineries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bouquineries` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Nom` varchar(25) CHARACTER SET latin1 NOT NULL,
  `Adresse` text CHARACTER SET latin1 NOT NULL,
  `AdresseComplete` text NOT NULL,
  `CodePostal` int(11) NOT NULL,
  `Ville` varchar(20) CHARACTER SET latin1 NOT NULL,
  `Pays` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT 'France',
  `Commentaire` text CHARACTER SET latin1 NOT NULL,
  `ID_Utilisateur` int(11) DEFAULT NULL,
  `CoordX` float NOT NULL DEFAULT '0',
  `CoordY` float NOT NULL DEFAULT '0',
  `DateAjout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demo`
--

DROP TABLE IF EXISTS `demo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demo` (
  `DateDernierInit` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emails_ventes`
--

DROP TABLE IF EXISTS `emails_ventes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails_ventes` (
  `username_achat` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `username_vente` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`username_achat`,`username_vente`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `magazines`
--

DROP TABLE IF EXISTS `magazines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `magazines` (
  `PaysAbrege` varchar(4) CHARACTER SET latin1 NOT NULL,
  `NomAbrege` varchar(7) CHARACTER SET latin1 NOT NULL,
  `NomComplet` varchar(70) COLLATE utf8_bin NOT NULL,
  `RedirigeDepuis` varchar(7) COLLATE utf8_bin DEFAULT NULL,
  `NeParaitPlus` tinyint(1) DEFAULT NULL,
  KEY `Index 1` (`PaysAbrege`,`NomAbrege`,`RedirigeDepuis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `numeros`
--

DROP TABLE IF EXISTS `numeros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `numeros` (
  `Pays` varchar(3) NOT NULL,
  `Magazine` varchar(6) NOT NULL,
  `Numero` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Etat` enum('mauvais','moyen','bon','indefini') NOT NULL,
  `ID_Acquisition` int(11) NOT NULL DEFAULT '-1',
  `AV` tinyint(1) NOT NULL,
  `ID_Utilisateur` int(11) NOT NULL,
  `DateAjout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `Pays` (`Pays`,`Magazine`,`Numero`,`ID_Utilisateur`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=110845 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parametres_listes`
--

DROP TABLE IF EXISTS `parametres_listes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parametres_listes` (
  `ID_Utilisateur` int(10) DEFAULT NULL,
  `Pays` varchar(3) COLLATE latin1_german2_ci DEFAULT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci DEFAULT NULL,
  `Type_Liste` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `Position_Liste` int(4) DEFAULT NULL,
  `Parametre` varchar(30) COLLATE latin1_german2_ci DEFAULT NULL,
  `Valeur` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  KEY `Index 1` (`ID_Utilisateur`,`Pays`,`Magazine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tranches_doublons`
--

DROP TABLE IF EXISTS `tranches_doublons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranches_doublons` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(8) COLLATE latin1_german2_ci NOT NULL,
  `NumeroReference` varchar(8) COLLATE latin1_german2_ci NOT NULL,
  UNIQUE KEY `Pays` (`Pays`,`Magazine`,`Numero`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tranches_pretes`
--

DROP TABLE IF EXISTS `tranches_pretes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranches_pretes` (
  `publicationcode` varchar(12) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `issuenumber` varchar(10) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `photographes` text COLLATE latin1_german2_ci,
  `createurs` text COLLATE latin1_german2_ci,
  `dateajout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`publicationcode`,`issuenumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `username` varchar(25) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(40) NOT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AccepterPartage` tinyint(1) NOT NULL,
  `DateInscription` date NOT NULL DEFAULT '0000-00-00',
  `EMail` varchar(50) NOT NULL,
  `BetaUser` tinyint(3) unsigned NOT NULL,
  `AfficherVideo` tinyint(1) NOT NULL DEFAULT '1',
  `Bibliotheque_Texture1` varchar(20) NOT NULL DEFAULT 'bois',
  `Bibliotheque_Sous_Texture1` varchar(50) NOT NULL DEFAULT 'HONDURAS MAHOGANY',
  `Bibliotheque_Texture2` varchar(20) NOT NULL DEFAULT 'bois',
  `Bibliotheque_Sous_Texture2` varchar(50) NOT NULL DEFAULT 'KNOTTY PINE',
  `Bibliotheque_Grossissement` double unsigned NOT NULL DEFAULT '1.5',
  `DernierAcces` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=1227 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


