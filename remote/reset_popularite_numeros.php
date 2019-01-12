<?php

include_once 'dm_client.php';
DmClient::init();
include_once 'auth.php';
require_once '../DucksManager_Core.class.php';

$requetes = explode(';', file_get_contents('reset_popularite_numeros.sql'));
foreach($requetes as $requete) {
    if (!empty($requete)) {
        echo '<pre>';print_r($requete);echo '</pre>';
        DM_Core::$d->requete($requete);
    }
}