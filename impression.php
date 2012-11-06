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

		ob_start();/*?>
		<table style="width: 100%; border: solid 1px black;">
			<tr>
				<td style="text-align: left;width:50%"><img src="proposition logo_fullsize.png" style="height: 100px;" /></td>
				<td style="text-align: center;width:50%"><?=COLLECTION_DE.$_SESSION['user']?></td>
			</tr>
		</table>
		<?php */
		$l->afficher($type);
		$html.= ob_get_contents();
		ob_end_clean();
		
		if (isset($_GET['output']) && $_GET['output'] == 'html') {
			echo $html;
		}
		else {
			$orientation = $type == 'collectable' ? 'L' : 'P';
	
			require_once('html2pdf/html2pdf.class.php');
			$html2pdf = new HTML2PDF($orientation,'A4','fr');
			$html2pdf->WriteHTML($html);
			$html2pdf->Output('exemple.pdf');
		}
	}
	else {
		echo 'Liste '.$type.' non trouvée';
	}
	
}
?>