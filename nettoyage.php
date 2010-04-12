<?php

require_once('Database.class.php');
$d=new Database();
if (!$d) {
	echo 'Probl&egrave;me avec la base de donn&eacute;es !';
	exit(-1);
}

$requete='SELECT Numéro FROM numeros WHERE Numéro LIKE \'0%\'';
$resultat=$d->requete_select($requete);
foreach($resultat as $numero) {
	$num=$numero['Numéro'];
	echo $num;
	if ($num!='0') {
		$num_change=preg_replace('#[0]+([^0]+)#is','$1',$num);
		$requete_update='UPDATE numeros SET Numéro='.$num_change.' WHERE Numéro LIKE \''.$num.'\'';
		echo $requete_update;
		$d->requete($requete_update);
	} 
}