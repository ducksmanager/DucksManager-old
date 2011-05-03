<?php
$dossier = 'upload/';
$fichier = basename($_FILES['image']['name']);
$taille_maxi = 400000;
$taille = filesize($_FILES['image']['tmp_name']);
$extensions = array('.png');
$extension = strrchr($_FILES['image']['name'], '.');
//Dιbut des vιrifications de sιcuritι...
if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
{
     $erreur = 'Vous devez uploader un fichier de type png...';
}
if($taille>$taille_maxi)
{
     $erreur = 'Le fichier est trop gros...';
}
if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
{
     //On formate le nom du fichier ici...
     $fichier = strtr($fichier,
          'ΐΑΒΓΔΕΗΘΙΚΛΜΝΞΟÒΣΤΥΦΩΪΫάέΰαβγδεηθικλμνξοπςστυφωϊϋόύÿ',
          'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
     $fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);
     if(move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ηa a fonctionnι...
     {
          echo 'Upload effectuι avec succθs !';
     }
     else //Sinon (la fonction renvoie FALSE).
     {
          echo 'Echec de l\'upload !';
     }
}
else
{
     echo $erreur;
}
?>