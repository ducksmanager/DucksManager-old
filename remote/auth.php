<?php

include_once('../ServeurDb.class.php');
ServeurDb::connect($database);
if (isset($_GET['debug']))
	echo 'Serveur : '.ServeurDb::getProfilCourant()->server
	.', User : '.ServeurDb::getProfilCourant()->user
	.', BD : '.$database."\n";

if (!isset($_GET['mdp']) || !ServeurDb::verifPassword($_GET['mdp'])) {
	echo 'Erreur d\'authentification';
	exit();
}

mysql_query('SET NAMES UTF8');