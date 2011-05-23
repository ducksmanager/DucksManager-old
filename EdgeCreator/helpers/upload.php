<?php
$url_root=getcwd();
$dossier = $url_root.'/../edges/'.$_POST['pays'].'/elements/';
$fichier = basename($_FILES['image']['name']);
$taille_maxi = 400000;
$taille = filesize($_FILES['image']['tmp_name']);
$extensions = array('.png');
$extension = strrchr($_FILES['image']['name'], '.');
//Dיbut des vיrifications de sיcuritי...
if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
{
	 $erreur = 'Vous devez uploader un fichier de type png...';
}
if($taille>$taille_maxi)
{
	 $erreur = 'Le fichier est trop gros...';
}
if (file_exists($dossier . $fichier)) {
	$erreur = 'Echec de l\'envoi : ce fichier existe d&eacute;j&agrave; ! '
			 .'Demandez &agrave; un admin de supprimer le fichier existant ou renommez le v&ocirc;tre !';
}
if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
{
	 //On formate le nom du fichier ici...
	 $fichier = strtr($fichier,
		  'ְֱֲֳִֵַָֹÊֻּֽ־ֿׂ׃װױײÙÚÛÜÝאבגדהוחטיךכלםמןנעףפץצשתûü‎ÿ',
		  'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
	 if(move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que חa a fonctionnי...
	 {
		  echo 'Envoi r&eacute;alis&eacute; avec succ&egrave;s !';
	 }
	 else //Sinon (la fonction renvoie FALSE).
	 {
		  echo 'Echec de l\'envoi !';
	 }
}
else
{
	 echo $erreur;
}
?>