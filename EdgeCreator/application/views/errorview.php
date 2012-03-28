<?php
$o = new stdClass();
$o->erreur='Erreur : '.$Erreur;
echo json_encode($o);