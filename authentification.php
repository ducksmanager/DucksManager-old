<?php
session_start();
$erreur='Identifiez vous.';
$user=isset($_POST['user']) ? $_POST['user'] : $_SESSION['user'];
$pass=isset($_POST['pass']) ? sha1($_POST['pass']) : $_SESSION['pass'];
if (isset($user)) {
	$requete_identifiants_valides='SELECT 1 FROM users WHERE username=\''.$user.'\' AND password=\''.$pass.'\'';
	$identifiants_valides=count(DM_Core::$d->requete_select($requete_identifiants_valides)) == 1;
	if ($identifiants_valides) {
		$requete_permission='SELECT 1 FROM edgecreator_droits WHERE username=\''.$user.'\' AND privilege=\'Admin\'';
		$permission_valide=count(DM_Core::$d->requete_select($requete_permission)) == 1;
		if ($permission_valide) {
			$_SESSION['user']=$user;
			$_SESSION['pass']=$pass;
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
?>