<?php header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
require_once('Liste.class.php');

if (!isset($_GET['type'])) {
	echo 'Type non spécifié';
}
else {
	$listes_existantes = Liste::set_types_listes();
	$type=$_GET['type'];
	if (in_array($type, array_keys($listes_existantes))) {
		$id_user=isset($_SESSION['user']) ? DM_Core::$d->user_to_id($_SESSION['user']) : null;
		$l=DM_Core::$d->toList($id_user);
		
		$l->afficher($type);
	}
	else {
		print_r($listes_existantes);
		echo 'Liste '.$type.' non trouvée';
	}
	
}
?>