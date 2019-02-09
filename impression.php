<?php header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
require_once 'Liste.class.php';

if (!isset($_GET['type'])) {
	echo 'Type non spécifié';
}
else {
	$listes_existantes = Liste::set_types_listes();
	$type=$_GET['type'];
	if (array_key_exists($type, $listes_existantes)) {
		$id_user=isset($_SESSION['user']) ? $_SESSION['id_user'] : null;
		$l=DM_Core::$d->toList($id_user);

		ob_start();
		$html = ''; ?>
		<table style="width: 90%;font-size: 15px">
			<tr>
				<td style="text-align: left;width:1%">
                    <a href="/?action=gerer"><img src="logo_petit.png" style="height: 100px;" /></a></td>
				<td style="text-align: center"><?=COLLECTION_DE.$_SESSION['user']?></td>
			</tr>
		</table>
		<?php	
		$l->afficher($type);
		$html.= ob_get_contents();
		ob_end_clean();
		
		echo $html;
	}
	else {
		echo 'Liste '.$type.' non trouvée';
	}
	
}
?>