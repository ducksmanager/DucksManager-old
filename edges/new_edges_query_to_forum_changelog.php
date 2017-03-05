<?header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once('../Database.class.php');
include_once('../authentification.php');

$requete_tranches_pretes_pour_publication = 'SELECT * FROM tranches_en_cours_modeles WHERE PretePourPublication=1';
$tranches_pretes_pour_publication = Inducks::requete_select($requete_tranches_pretes_pour_publication, ServeurDb::$nom_db_DM, 'serveur_virtuel');

$urls_images= [];
$numeros= [];
foreach($tranches_pretes_pour_publication as $tranche) {
    $pays = $tranche['Pays'];
    $id = $tranche['ID'];
    $magazine = $tranche['Magazine'];
    $publicationcode = $pays.'/'.$magazine;
    $numero = $tranche['Numero'];
    $photographes = $tranche['photographes'];
    $createurs = $tranche['createurs'];

    $valeurs= [];
    $valeurs['publicationcode']=$publicationcode;
    $valeurs['issuenumber']=$numero;

    $chemin = $pays .'/gen/'. $magazine .'.'. $numero .'.png';

    $url = ServeurDb::getUrlServeurVirtuel().'/DucksManager/edges/'.$chemin;

    if (isset($_GET['publier'])) {
        $requete='INSERT INTO tranches_pretes ('.implode(',',array_keys($valeurs)).') VALUES (\''.implode($valeurs, '\', \'').'\')';
        DM_Core::$d->requete($requete);

        foreach($photographes as $utilisateur_photographe) {
            $requete = 'SELECT ID FROM users WHERE username=\''.$utilisateur_photographe.'\'';
            $resultat = DM_Core::$d->requete_select($requete);
            $id_utilisateur = $resultat[0]['ID'];

            $requete="INSERT INTO tranches_pretes_contributeurs(publicationcode, issuenumber, contributeur, contribution) 
                      VALUES ('$publicationcode','$numero',$id_utilisateur,'photographe')";
            DM_Core::$d->requete($requete);
        }

        foreach($createurs as $utilisateur_createur) {
            $requete = 'SELECT ID FROM users WHERE username=\''.$utilisateur_createur.'\'';
            $resultat = DM_Core::$d->requete_select($requete);
            $id_utilisateur = $resultat[0]['ID'];

            $requete="INSERT INTO tranches_pretes_contributeurs(publicationcode, issuenumber, contributeur, contribution) 
                      VALUES ('$publicationcode','$numero',$id_utilisateur,'createur')";
            DM_Core::$d->requete($requete);
        }

        copy($url, $chemin);

        $requete_tranche_publiee = 'UPDATE tranches_en_cours_modeles SET PretePourPublication=0 WHERE ID='.$id;
        $tranches_pretes_pour_publication = Inducks::requete_select($requete_tranche_publiee, ServeurDb::$nom_db_DM, 'serveur_virtuel');

    }
    $urls_images[]=$url;
    if (!array_key_exists($publicationcode,$numeros))
        $numeros[$publicationcode]= ['numeros'=> [], 'contributeurs'=> []];
    $numeros[$publicationcode]['numeros'][]=$numero;
    if (!is_null($photographes)) {
        $numeros[$publicationcode]['contributeurs'][]=$photographes;
    }
    if (!is_null($createurs)) {
        $numeros[$publicationcode]['contributeurs'][]=$createurs;
    }
}

$noms_pays = Inducks::get_noms_complets_pays(array_keys($numeros));
$noms_magazines = Inducks::get_noms_complets_magazines(array_keys($numeros));

$code_ajouts = [];
$code_images_ajouts = [];
$contributeurs_non_remercies = ['brunoperel'];
foreach($numeros as $publicationcode=>$numeros_et_contributeurs) {
    list($pays,$magazine)=explode('/',$publicationcode);
    $code_ajout ='[Biblioth&egrave;que][Tranches][Ajout]'
        .$noms_magazines[$publicationcode]
        .($pays=='fr' ? '':' ('.$noms_pays[$pays].')')
        .' n&deg; '.implode(', ',$numeros_et_contributeurs['numeros']);

    $contributeurs=array_diff($numeros_et_contributeurs['contributeurs'], $contributeurs_non_remercies);
    if (count($contributeurs) > 0) {
        $contributeurs = array_unique($contributeurs);
        $code_ajout.= ' (Merci '.implode(', ',$contributeurs).')';
    }
    $code_ajouts[]=$code_ajout;
    foreach($numeros_et_contributeurs['numeros'] as $numero) {
        $code_images_ajouts[] = '[img]http://ducksmanager.net/edges/' . $pays . '/gen/' . $magazine . '.' .$numero.'.png?C[/img]';
    }
}
echo '<pre>[code]'.implode('<br />',$code_ajouts).'[/code]</pre>';
echo '<pre style="white-space: normal">'.implode('',$code_images_ajouts).'</pre>';
echo '<br /><br />';
foreach ($urls_images as $url_image) {
    ?><img src="<?=$url_image?>" /><?php
}

?><br /><br /><?php

if (isset($_GET['publier'])) {
    ?>Publication OK<?php
}
else {
    ?>
    <a href="?publier">Ajouter ces images</a>
    <?php
}