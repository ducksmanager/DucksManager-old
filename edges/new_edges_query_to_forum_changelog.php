<?header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once('../Database.class.php');

if (!isset($_SESSION['user'])) {
	$erreur='Identifiez vous.';
	if (isset($_POST['user'])) {
		$requete_identifiants_valides='SELECT 1 FROM users WHERE username=\''.$_POST['user'].'\' AND password=sha1(\''.$_POST['pass'].'\')';
		$identifiants_valides=count(DM_Core::$d->requete_select($requete_identifiants_valides)) == 1;
		if ($identifiants_valides) {
			$requete_permission='SELECT 1 FROM edgecreator_droits WHERE username=\''.$_POST['user'].'\' AND privilege=\'Admin\'';
			$permission_valide=count(DM_Core::$d->requete_select($requete_permission)) == 1;
			if ($permission_valide) {
				$_SESSION['user']=$_POST['user'];
				$erreur='';
			}
			else {
				$erreur = 'Permission non accord&eacute;e';
			}
		}
		else {
			$erreur = 'Identifiants invalides';
		}
	}
	if (!empty($erreur)) {
		?>
		<html>
			<body>
				<?=$erreur?>
				<form method="post" action="">
					<table border="0">
						<tr><td>Nom d'utilisateur :</td><td><input type="text" name="user" /></td></tr>
						<tr><td>Mot de passe :</td><td><input type="password" name="pass" /></td></tr>
						<tr><td align="center" colspan="2"><input type="submit" value="Connexion"/></td></tr>
					</table>
				</form>
			</body>
		</html>
		<?php exit(0);
	}
}
?>
<form action="" method="post">
<textarea name="query" rows="20" cols="150">
	<?php if (isset($_POST['query'])) {
		echo $_POST['query'];
	}?>
</textarea>
<input type="submit" value="Go" />
</form>

<?php
if (isset($_POST['query'])) {
	include_once('../Database.class.php');
	$numeros=array();
	$abc=array();
	for ($i='A';$i<='Z' && strlen($i)==1;$i++)
		$abc[]=$i;
	
	$lignes=explode("\r\n",$_POST['query']);
	foreach($lignes as $ligne) {
		if (!empty($ligne)) {
			$regex="#INSERT INTO `tranches_pretes` VALUES \('([^']+)', '([^']+)', (?:(?:NULL)|(?:'[0-9,]*')), (?:(?:NULL)|(?:'[0-9,]*')), #";
			preg_match($regex,$ligne,$matches);
			list($pays,$magazine)=explode('/',$matches[1]);
			$numero=$matches[2];
			$url='http://ducksmanager.net/edges/'.$pays.'/gen/'.$magazine.'.'.$numero.'.png?'.$abc[rand(0,25)];
			$balise='[img]'.$url.'[/img]';
			echo $balise;
			if (!array_key_exists($pays.'/'.$magazine,$numeros))
				$numeros[$pays.'/'.$magazine]=array();
			$numeros[$pays.'/'.$magazine][]=$numero;
		}
	}
	echo '<br /><br />';
	$noms_magazines=array();
	foreach($numeros as $pays_magazine=>$numeros_associes) {
		list($pays,$magazine)=explode('/',$pays_magazine);
		list($nom_complet_pays,$nom_complet_magazine)=DM_Core::$d->get_nom_complet_magazine($pays,$magazine);
		echo '[Biblioth&egrave;que][Tranches][Ajout]'
			.$nom_complet_magazine
			.($nom_complet_pays=='France' ? '':('('.utf8_encode($nom_complet_magazine).')'))
			.' n&deg; '.implode(', ',$numeros_associes).'<br />';
	}
}
?>