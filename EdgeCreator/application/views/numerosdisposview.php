<?php
$envoi=new stdClass();
switch($mode) {
	case 'get_pays':
		$envoi->pays=$pays;
	break;
	case 'get_magazines':
		$envoi->magazines=$magazines;
	break;
	case 'get_numeros':
		$envoi->numeros_dispos=$numeros_dispos;
		$envoi->tranches_pretes=$tranches_pretes;
		$envoi->nb_numeros_dispos=count($numeros_dispos);
		$envoi->nb_etapes=$nb_etapes;
		$envoi->nom_magazine=$nom_magazine;
	break;
}
echo json_encode($envoi);
?>