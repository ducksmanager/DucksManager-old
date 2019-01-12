<?php
if (isset($_GET['dbg'])) {
	error_reporting(E_ALL);
}
$database= $_GET['db'] ?? 'coa';

include_once 'auth.php';

if (isset($_GET['req'])) {
	$requete=str_replace("\'","'",$_GET['req']);
	if (isset($_GET['params'])) {
	    $params = json_decode($_GET['params']);
	    $resultats = DM_Core::$d->requete_select($requete, array_map(function ($typeAndValue) {
            return $typeAndValue->value;
        }, $params));
    }
	else {
        $resultats = DM_Core::$d->requete_select($requete);
    }
    header('Content-Type: text/html; charset=utf-8');
	if (count($resultats) === 0) {
	    echo serialize([[], []]);
    }
    else {
        echo serialize([array_keys($resultats[0]), array_map('array_values', $resultats)]);
    }
}
else {
    echo 'Pas de requete';
}