<?php
include_once '../Inducks.class.php';

$regex_numeros_JM_valides='#[0-9]+#is';
$numero_reference=2963;
$requete='SELECT issuenumber FROM inducks_issue '
		.'WHERE publicationcode=\'fr/JM\' '
		.'  AND issuenumber REGEXP \'^[0-9]+$\' '
		.'  AND CAST(issuenumber AS UNSIGNED) > CAST('.$numero_reference.' AS UNSIGNED)';

$doublons_coa=DM_Core::$d->requete_select($requete, [], 'db_coa');

$requete_doublons_deja_dispo="SELECT Numero FROM tranches_doublons "
							."WHERE NumeroReference=$numero_reference "
							."  AND CONCAT(Pays,'/',Magazine)='fr/JM'";
if (isset($_GET['dbg'])) {
    echo $requete_doublons_deja_dispo;
}
$resultats_doublons_deja_dispo=DM_Core::$d->requete_select($requete_doublons_deja_dispo);

if (isset($_GET['dbg'])) {
	print_r( $resultats_doublons_deja_dispo);
}

$doublons_deja_dispo= [];
$doublons_a_ajouter= [];

foreach($resultats_doublons_deja_dispo as $doublon_deja_dispo) {
	$doublons_deja_dispo[$doublon_deja_dispo['Numero']]=true;
}
if (isset($_GET['dbg'])) {
	echo 'Doublons deja dispos : <br />';
	echo '<pre>';print_r($doublons_deja_dispo);echo '</pre>';

}

foreach($doublons_coa as $doublon_coa) {
	if (!array_key_exists($doublon_coa['issuenumber'],$doublons_deja_dispo)) {
        $doublons_a_ajouter[] = $doublon_coa['issuenumber'];
    }
}
if (count($doublons_a_ajouter) > 0) {
	$requete_ajout_doublons='INSERT INTO tranches_doublons(Pays,Magazine,Numero,NumeroReference) '
						   .'VALUES ';
	$mini_requetes_ajout= [];
	foreach($doublons_a_ajouter as $doublon) {
        $mini_requetes_ajout[] = "('fr','JM','$doublon','$numero_reference')";
    }
	
	$requete_ajout_doublons.=implode(',',$mini_requetes_ajout);
	
	if (isset($_GET['dbg'])) {
        echo $requete_ajout_doublons . '<br />';
    }
    DM_Core::$d->requete($requete_ajout_doublons);
}

$requete_tranches_deja_pretes="SELECT issuenumber FROM tranches_pretes "
							."WHERE publicationcode='fr/JM' ";
$resultats_tranches_deja_pretes=DM_Core::$d->requete_select($requete_tranches_deja_pretes);
$tranches_deja_dispo= [];
$tranches_a_ajouter= [];

foreach($resultats_tranches_deja_pretes as $tranche_deja_dispo) {
	$tranches_deja_dispo[$tranche_deja_dispo['issuenumber']]=true;
}

foreach($doublons_a_ajouter as $doublon_a_ajouter) {
	if (!array_key_exists($doublon_a_ajouter,$tranches_deja_dispo)) {
        $tranches_a_ajouter[] = $doublon_a_ajouter;
    }
}
if (count($tranches_a_ajouter) > 0) {
	$mini_requetes_ajout= [];
	foreach($tranches_a_ajouter as $numero) {
        $mini_requetes_ajout[] = "('fr/JM','$numero',NOW())";
    }
	
	$requete_ajout_tranches.='INSERT INTO tranches_pretes(publicationcode,issuenumber,dateajout) '
							.'VALUES '.implode(',',$mini_requetes_ajout);
	
	if (isset($_GET['dbg'])) {
        echo $requete_ajout_tranches . '<br />';
    }
    DM_Core::$d->requete($requete_ajout_tranches);
}