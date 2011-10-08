<?php
include_once('Inducks.class.php');
Inducks::$use_local_db=false;
DatabasePriv::connect('coa');

mysql_query('SET NAMES UTF8');


$regex_numeros_JM_valides='#[0-9]+#is';
$numero_reference=2963;
$requete='SELECT issuenumber FROM inducks_issue '
		.'WHERE publicationcode=\'fr/JM\' '
		.'  AND issuenumber REGEXP \'^[0-9]+$\' '
		.'  AND CAST(issuenumber AS UNSIGNED) > CAST('.$numero_reference.' AS UNSIGNED)';

$doublons_coa=Inducks::requete_select($requete,'coa','serveur_virtuel');

$requete_doublons_deja_dispo="SELECT Numero FROM tranches_doublons "
							."WHERE NumeroReference=$numero_reference "
							."  AND CONCAT(Pays,'/',Magazine)='fr/JM'";
$resultats_doublons_deja_dispo=Inducks::requete_select($requete_doublons_deja_dispo,'db301759616','ducksmanager.net');
$doublons_deja_dispo=array();
$doublons_a_ajouter=array();

foreach($resultats_doublons_deja_dispo as $doublon_deja_dispo) {
	$doublons_deja_dispo[$doublon_deja_dispo['Numero']]=true;
}

foreach($doublons_coa as $doublon_coa) {
	if (!array_key_exists($doublon_coa['issuenumber'],$doublons_deja_dispo))
		$doublons_a_ajouter[]=$doublon_coa['issuenumber'];
}
if (count($doublons_a_ajouter) > 0) {
	$requete_ajout_doublons='INSERT INTO tranches_doublons(Pays,Magazine,Numero,NumeroReference) '
						   .'VALUES ';
	$mini_requetes_ajout=array();
	foreach($doublons_a_ajouter as $doublon)
		$mini_requetes_ajout[]="('fr','JM','$doublon','$numero_reference')";
	
	$requete_ajout_doublons.=implode(',',$mini_requetes_ajout);
	
	//Inducks::requete_select($requete_ajout_doublons,'db301759616','ducksmanager.net');
	echo $requete_ajout_doublons.'<br />';
}

$requete_tranches_deja_pretes="SELECT issuenumber FROM tranches_pretes "
							."WHERE publicationcode='fr/JM' ";
$resultats_tranches_deja_pretes=Inducks::requete_select($requete_tranches_deja_pretes,'db301759616','ducksmanager.net');
$tranches_deja_dispo=array();
$tranches_a_ajouter=array();

foreach($resultats_tranches_deja_pretes as $tranche_deja_dispo) {
	$tranches_deja_dispo[$tranche_deja_dispo['issuenumber']]=true;
}

foreach($doublons_a_ajouter as $doublon_a_ajouter) {
	if (!array_key_exists($doublon_a_ajouter,$tranches_deja_dispo))
		$tranches_a_ajouter[]=$doublon_a_ajouter;
}
if (count($tranches_a_ajouter) > 0) {
	$requete_ajout_tranches='INSERT INTO tranches_pretes(publicationcode,issuenumber,photographes,createurs,dateajout) '
						   .'VALUES ';
	$mini_requetes_ajout=array();
	foreach($tranches_a_ajouter as $numero)
		$mini_requetes_ajout[]="('fr/JM','$numero',NULL,152,NOW())";
	
	$requete_ajout_tranches.=implode(',',$mini_requetes_ajout);
	
	//Inducks::requete_select($requete_ajout_tranches,'db301759616','ducksmanager.net');
	echo $requete_ajout_tranches;
}
$a=1;