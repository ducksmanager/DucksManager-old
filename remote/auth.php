<?php

if (!isset($_GET['mdp']) || sha1(DmClient::$dm_server->password) !== $_GET['mdp']) {
    echo 'Erreur d\'authentification';
    exit();
}