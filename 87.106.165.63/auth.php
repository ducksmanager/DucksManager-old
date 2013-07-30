<?php

include_once('_priv/Database.priv.class.php');
DatabasePriv::connect($database);
if (isset($_GET['debug']))
	echo 'Serveur : '.DatabasePriv::getProfilCourant()->server
	.', User : '.DatabasePriv::getProfilCourant()->user
	.', BD : '.$database."\n";

if (!isset($_GET['mdp']) || !DatabasePriv::verifPassword($_GET['mdp'])) {
	echo 'Erreur d\'authentification';
	exit();
}

mysql_query('SET NAMES UTF8');