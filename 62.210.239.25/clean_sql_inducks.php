<?php
$no_database=true;
include_once('Util.class.php');
$sql=Util::lire_depuis_fichier('./inducks/createtables.sql');
$sql=  preg_replace('#DROP TABLE IF EXISTS induckspriv[^;]+;#is', '', $sql);
$sql=  preg_replace('#RENAME TABLE induckspriv[^;]+;#is', '', $sql);
$sql=  preg_replace('#CREATE TABLE IF NOT EXISTS induckspriv[^;]+;#is', '', $sql);
$sql=  preg_replace('#LOAD DATA LOCAL INFILE "\./isv/induckspriv[^;]+;#is', '', $sql);
$sql=  preg_replace('#CREATE TABLE induckspriv_[^;]+;#is', '', $sql);
$sql=  preg_replace('#\# SQL for re-creating and filling table induckspriv_[a-z]*#is', '', $sql);
Util::ecrire_dans_fichier('./inducks/createtables_clean.sql', $sql, false);
?>
