<?php
$envoi=new stdClass();
$envoi->numeros_dispos=$numeros_dispos;
$envoi->tranches_pretes=$tranches_pretes;
$envoi->nb_numeros_dispos=count($numeros_dispos);
$envoi->nb_etapes=$nb_etapes;
$envoi->nom_magazine=$nom_magazine;
header("X-JSON: " . json_encode($envoi));
?>