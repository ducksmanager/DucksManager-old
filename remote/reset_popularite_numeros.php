<?php

include_once 'auth.php';

$requetes = explode(';', file_get_contents('reset_popularite_numeros.sql'));
foreach($requetes as $requete) {
    Database::$handle->query($requete);
}