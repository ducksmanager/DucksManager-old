<?php include_once('../Database.class.php');
if (isLocalhost()) {?>
	<form method="get">
		Pays : <input type="text" name="pays" />
		<input type="submit" value="OK" />
	</form>
	<?php
}
if (isset($_GET['nouveau_nom']) && !empty($_GET['nouveau_nom'])) {
	$pays=$_GET['pays'];
	$rename_file=$_GET['rename_file'] !== 'false';
	$ancien_nom=mysql_real_escape_string($_GET['ancien_nom']);
	$nouveau_nom=mysql_real_escape_string($_GET['nouveau_nom']);
	if ($rename_file) {
		$filename=$pays.'/elements/'.$ancien_nom;
		$filenewname=$pays.'/elements/'.$nouveau_nom;
	}
	if (!$rename_file || rename($filename,$filenewname)) {
		if ($rename_file) {
			echo 'Renommage OK';
		}
		$requete_maj='UPDATE edgecreator_valeurs SET Option_valeur=\''.$nouveau_nom.'\' WHERE Option_valeur=\''.$ancien_nom.'\'';
		DM_Core::$d->requete($requete_maj);
		
		$requete_maj_tranches_en_cours='UPDATE tranches_en_cours_valeurs SET Option_valeur=\''.$nouveau_nom.'\' WHERE Option_valeur=\''.$ancien_nom.'\'';
		DM_Core::$d->requete($requete_maj_tranches_en_cours);
		
		echo '<br /><textarea cols="150" rows="5">';
		echo $requete_maj_tranches_en_cours."\n";
		echo $requete_maj;
		echo '</textarea>';
		
		if (isLocalhost()) {
			$url_complete_serveur_virtuel = DatabasePriv::$url_serveur_virtuel
										   .substr($_SERVER["REQUEST_URI"],strpos($_SERVER["REQUEST_URI"],'/',2));
			?><br /><iframe style="width:800px; height: 200px" src="<?=$url_complete_serveur_virtuel?>"></iframe><?php
		}		
	}
}
if (isLocalhost() && isset($_GET['pays']) && !empty($_GET['pays'])) {
	$pays=$_GET['pays'];
	?>
	<form method="get">
		<input type="hidden" name="pays" value="<?=$pays?>"/>
		Ancien nom : <select name="ancien_nom" onchange="document.getElementById('nouveau_nom').value=this.value">
		<?php
		$rep=$pays.'/elements/';
		$dir = opendir($rep);
		while ($f = readdir($dir)) {
			if (strpos($f,'.png')===false
			 && strpos($f,'.jpg')===false )
				continue;
			if(is_file($rep.$f)) {
				$nom=$f;
				echo '<option>'.utf8_encode($nom).'</option>';
			}
		}
		?>
		</select>
		Nouveau nom : <input type="text" id="nouveau_nom" name="nouveau_nom" />
		<input type="submit" value="OK" />
	</form>
<?php
}
?>