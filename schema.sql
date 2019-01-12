SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db301759616`
--

-- --------------------------------------------------------

--
-- Table structure for table `achats`
--

CREATE TABLE IF NOT EXISTS `achats` (
  `ID_Acquisition` int(11) NOT NULL AUTO_INCREMENT,
  `ID_User` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Style_couleur` varchar(9) DEFAULT NULL,
  `Style_soulignement` enum('Aucun','Simple','Double','Triple','Pointillé','Zig-zag','Double zig-zag','Ondulé','Double ondulé') DEFAULT NULL,
  `Style_entourage` enum('Aucun','Simple','Double','Pointillé','Rectangulaire') DEFAULT NULL,
  `Style_marquage` enum('Aucun','*','+','!') DEFAULT NULL,
  `Description` varchar(100) NOT NULL,
  UNIQUE KEY `Acquisition,Date est unique` (`ID_Acquisition`,`Date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1235 ;

-- --------------------------------------------------------

--
-- Table structure for table `auteurs`
--

CREATE TABLE IF NOT EXISTS `auteurs` (
  `ID_auteur` int(11) NOT NULL AUTO_INCREMENT,
  `NomAuteur` varchar(20) CHARACTER SET latin1 NOT NULL,
  `NbHistoires` int(11) NOT NULL,
  `NbHistoires_old` int(11) NOT NULL,
  `DateMAJ` date NOT NULL,
  PRIMARY KEY (`ID_auteur`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `auteurs_pseudos`
--

CREATE TABLE IF NOT EXISTS `auteurs_pseudos` (
	NomAuteur varchar(50) charset utf8 not null,
	ID_user int not null,
	Notation tinyint(1) not null
)
engine=MyISAM collate=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `bibliotheque_contributeurs`
--

CREATE TABLE IF NOT EXISTS `bibliotheque_contributeurs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Nom` varchar(30) COLLATE latin1_german2_ci DEFAULT NULL,
  `Texte` text COLLATE latin1_german2_ci,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Table structure for table `bibliotheque_options`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `bibliotheque_ordre_magazines`
--

CREATE TABLE IF NOT EXISTS `bibliotheque_ordre_magazines` (
  `Pays` varchar(3) COLLATE latin1_german2_ci DEFAULT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci DEFAULT NULL,
  `Ordre` int(3) DEFAULT NULL,
  `ID_Utilisateur` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bouquineries`
--

CREATE TABLE IF NOT EXISTS `bouquineries` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Table structure for table `bouquineries_exemples`
--

CREATE TABLE IF NOT EXISTS `bouquineries_exemples` (
  `ID_Bouquinerie` int(11) NOT NULL,
  `PaysNumero` varchar(4) NOT NULL,
  `MagazineNumero` varchar(7) NOT NULL,
  `Numero` int(11) NOT NULL,
  `Prix` smallint(6) NOT NULL,
  `Etat` enum('Passable','Moyen','Bon','Excellent','Indéfini') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `demo`
--

CREATE TABLE IF NOT EXISTS `demo` (
  `DateDernierInit` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emails_ventes`
--

CREATE TABLE IF NOT EXISTS `emails_ventes` (
  `username_achat` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `username_vente` varchar(50) COLLATE latin1_german2_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`username_achat`,`username_vente`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `magazines`
--

CREATE TABLE IF NOT EXISTS `magazines` (
  `PaysAbrege` varchar(4) CHARACTER SET latin1 NOT NULL,
  `NomAbrege` varchar(7) CHARACTER SET latin1 NOT NULL,
  `NomComplet` varchar(70) COLLATE utf8_bin NOT NULL,
  `RedirigeDepuis` varchar(7) COLLATE utf8_bin DEFAULT NULL,
  `NeParaitPlus` tinyint(1) DEFAULT NULL,
  KEY `Index 1` (`PaysAbrege`,`NomAbrege`,`RedirigeDepuis`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `numeros`
--

CREATE TABLE IF NOT EXISTS `numeros` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Etat` enum('mauvais','moyen','bon','indefini') COLLATE latin1_german2_ci NOT NULL,
  `ID_Acquisition` int(11) NOT NULL DEFAULT '-1',
  `AV` tinyint(1) NOT NULL,
  `ID_Utilisateur` int(11) NOT NULL,
  `DateAjout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Pays` (`Pays`,`Magazine`,`Numero`,`ID_Utilisateur`),
  KEY `Utilisateur` (`ID_Utilisateur`),
  KEY `Pays_Magazine_Numero` (`Pays`,`Magazine`,`Numero`),
  KEY `Pays_Magazine_Numero_DateAjout` (`DateAjout`,`Pays`,`Magazine`,`Numero`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=222548 ;

-- --------------------------------------------------------

--
-- Table structure for table `numeros_popularite`
--

CREATE TABLE IF NOT EXISTS `numeros_popularite` (
  `Pays` varchar(3) NOT NULL,
  `Magazine` varchar(6) NOT NULL,
  `Numero` varchar(8) NOT NULL,
  `Popularite` int(11) NOT NULL,
  PRIMARY KEY (`Pays`,`Magazine`,`Numero`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `numeros_recommandes`
--

CREATE TABLE IF NOT EXISTS `numeros_recommandes` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(10) COLLATE latin1_german2_ci NOT NULL,
  `Notation` tinyint(3) NOT NULL,
  `ID_Utilisateur` int(11) NOT NULL,
  `Texte` text COLLATE latin1_german2_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parametres_listes`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `pays`
--

CREATE TABLE IF NOT EXISTS `pays` (
  `NomAbrege` varchar(10) COLLATE latin1_german2_ci DEFAULT NULL,
  `NomComplet` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `L10n` varchar(5) COLLATE latin1_german2_ci DEFAULT 'fr',
  UNIQUE KEY `Cle` (`NomAbrege`,`NomComplet`,`L10n`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `traitement_stats`
--

CREATE TABLE IF NOT EXISTS `traitement_stats` (
  `Auteur` varchar(20) NOT NULL,
  `Page` int(11) NOT NULL,
  `NbHistoires` int(11) NOT NULL,
  `NbHistoires_tmp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tranches_doublons`
--

CREATE TABLE IF NOT EXISTS `tranches_doublons` (
  `Pays` varchar(3) COLLATE latin1_german2_ci NOT NULL,
  `Magazine` varchar(6) COLLATE latin1_german2_ci NOT NULL,
  `Numero` varchar(8) COLLATE latin1_german2_ci NOT NULL,
  `NumeroReference` varchar(8) COLLATE latin1_german2_ci NOT NULL,
  `TrancheReference` int(11) DEFAULT NULL,
  UNIQUE KEY `tranches_doublons_reference` (`Pays`,`Magazine`,`Numero`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tranches_pretes`
--

CREATE TABLE IF NOT EXISTS `tranches_pretes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `publicationcode` varchar(12) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `issuenumber` varchar(10) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `dateajout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `tranchespretes_unique` (`publicationcode`,`issuenumber`),
  KEY `tranches_pretes_publicationcode_issuenumber_index` (`publicationcode`,`issuenumber`),
  KEY `tranches_pretes_dateajout_index` (`dateajout`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=5993 ;

-- --------------------------------------------------------

--
-- Table structure for table `tranches_pretes_contributeurs`
--

CREATE TABLE IF NOT EXISTS `tranches_pretes_contributeurs` (
  `publicationcode` varchar(15) NOT NULL,
  `issuenumber` varchar(30) NOT NULL,
  `contributeur` int(11) NOT NULL,
  `contribution` enum('photographe','createur') NOT NULL DEFAULT 'createur',
  PRIMARY KEY (`publicationcode`,`issuenumber`,`contributeur`,`contribution`),
  KEY `tranches_pretes_contributeurs_publicationcode_issuenumber_index` (`publicationcode`,`issuenumber`),
  KEY `tranches_pretes_contributeurs_contributeur_index` (`contributeur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tranches_pretes_old`
--

CREATE TABLE IF NOT EXISTS `tranches_pretes_old` (
  `publicationcode` varchar(12) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `issuenumber` varchar(10) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `photographes` text COLLATE latin1_german2_ci,
  `createurs` text COLLATE latin1_german2_ci,
  `dateajout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`publicationcode`,`issuenumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tranches_previews`
--

CREATE TABLE IF NOT EXISTS `tranches_previews` (
  `ID_Session` varchar(32) COLLATE latin1_german2_ci DEFAULT NULL,
  `ID_Preview` int(11) NOT NULL AUTO_INCREMENT,
  `Options` varchar(2000) COLLATE latin1_german2_ci DEFAULT '0',
  KEY `Index 1` (`ID_Preview`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(40) CHARACTER SET latin1 NOT NULL,
  `AccepterPartage` tinyint(1) NOT NULL DEFAULT '1',
  `DateInscription` date NOT NULL DEFAULT '0000-00-00',
  `EMail` varchar(50) CHARACTER SET latin1 NOT NULL,
  `RecommandationsListeMags` tinyint(1) NOT NULL DEFAULT '1',
  `BetaUser` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `AfficherVideo` tinyint(1) NOT NULL DEFAULT '1',
  `Bibliotheque_Texture1` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT 'bois',
  `Bibliotheque_Sous_Texture1` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT 'HONDURAS MAHOGANY',
  `Bibliotheque_Texture2` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT 'bois',
  `Bibliotheque_Sous_Texture2` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT 'KNOTTY PINE',
  `Bibliotheque_Grossissement` double unsigned NOT NULL DEFAULT '1.5',
  `DernierAcces` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1485 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) COLLATE latin1_german2_ci NOT NULL,
  `role` varchar(20) COLLATE latin1_german2_ci NOT NULL,
  `privilege` enum('Admin','Edition','Affichage') COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`,`role`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1370 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_points`
--

CREATE TABLE IF NOT EXISTS `users_points` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_Utilisateur` int(11) DEFAULT NULL,
  `TypeContribution` char(11) CHARACTER SET utf8 DEFAULT NULL,
  `NbPoints` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=43 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;