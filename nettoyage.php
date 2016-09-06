<?php

require_once('Database.class.php');
$d=new Database(true);
if (!$d) {
	echo PROBLEME_BD;
	exit(-1);
}

$requete='SELECT Num�ro FROM numeros WHERE Num�ro LIKE \'0%\'';
$resultat=$d->requete_select_distante($requete);
foreach($resultat as $numero) {
	$num=$numero['Num�ro'];
	echo $num;
	if ($num!='0') {
		$num_change=preg_replace('#[0]+([^0]+)#is','$1',$num);
		$requete_update='UPDATE numeros SET Num�ro='.$num_change.' WHERE Num�ro = \''.$num.'\'';
		echo $requete_update;
		$d->requete_distante($requete_update);
	} 
}