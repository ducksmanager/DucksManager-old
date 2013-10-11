<?php
$url_root=getcwd();
$est_photo_tranche = $_POST['photo_tranche'] == 1;
$extension = strtolower(strrchr($_FILES['image']['name'], '.'));
$extension_cible='.jpg';
$dossier = $url_root.'/../edges/'.$_POST['pays'].'/'.( $est_photo_tranche ? 'photos' : 'elements' ).'/';

if ($est_photo_tranche) {
	$fichier=$_POST['magazine'].'.'.$_POST['numero'].'.photo';
	$i=1;
	while (file_exists($dossier.$fichier.'_'.$i.$extension_cible)) {
		$i++;
	}
	$fichier.='_'.$i;
	$fichier.=$extension_cible;
}
else {
	$fichier = basename($_FILES['image']['name']);
}
$taille_maxi = $_POST['MAX_FILE_SIZE'];
$taille = filesize($_FILES['image']['tmp_name']);
$extensions = $est_photo_tranche ? array('.jpg','.jpeg') : array('.png');
//Début des vérifications de sécurité...
if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
{
	 $erreur = 'Vous devez uploader un fichier de type '.implode(' ou ',$extensions);
}
if($taille>$taille_maxi)
{
	 $erreur = 'Le fichier est trop gros.';
}
if (file_exists($dossier . $fichier)) {
	$erreur = 'Echec de l\'envoi : ce fichier existe d&eacute;j&agrave; ! '
			 .'Demandez &agrave; un admin de supprimer le fichier existant ou renommez le v&ocirc;tre !';
}
if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
{
	 //On formate le nom du fichier ici...
	 $fichier = strtr($fichier,
		  'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
		  'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
	 
	 if (@opendir($dossier) === false) {
	 	mkdir($dossier,0777,true);
	 }
	 if(move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
	 {
		  if ($est_photo_tranche) {
	  		if ($extension == '.png') {
		  		$im=imagecreatefrompng($dossier . $fichier);
		  		unlink($dossier . $fichier);
		  		$fichier=str_replace('.png','.jpg',$fichier);
		  		imagejpeg($im, $dossier . $fichier);
		  	}
	  		list($width, $height, $type, $attr) = getimagesize($dossier . $fichier);
	  		if ($width > $height) { // Image photographiée à l'horizontale
	  			$im=imagecreatefromjpeg($dossier . $fichier);
	  				
				$fond=imagecolorallocatealpha($im, 255, 255, 255, 127);
				$im=imagerotate($im, 90, $fond);
	  			imagejpeg($im, $dossier . $fichier, 100);	  				
	  		}
	  		?>
	  		<script type="text/javascript">
			if (window.parent.document.getElementById('wizard-photos').parentNode.style.display === 'block') {
				window.parent.lister_images_gallerie('Photos');
			}
			else {
				window.parent.afficher_photo_tranche();
			}
	  		</script><?php
		  }
		  ?>Envoi r&eacute;alis&eacute; avec succ&egrave;s !<?php 
		  if ($est_photo_tranche) {
			afficher_retour();
		  }	
	 }
	 else //Sinon (la fonction renvoie FALSE).
	 {
		  echo 'Echec de l\'envoi !';
	 	  afficher_retour();
	 }
}
else
{
	 echo $erreur;
	 afficher_retour();
}

function afficher_retour() {
	?><br /><a href="javascript:void(0)" onclick="location.href=location.href.replace(/\/upload\.php/g,'/image_upload.php')">Autre envoi</a><?php
	
}
?>