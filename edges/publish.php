<?header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once '../Database.class.php';
include_once '../authentification.php';

$requete_tranches_pretes_pour_publication = '
  SELECT ID, Pays, Magazine, Numero
  FROM tranches_en_cours_modeles modeles
  WHERE Active=1
  ORDER BY Pays, Magazine, Numero';
$tranches_pretes_pour_publication = DM_Core::$d->requete($requete_tranches_pretes_pour_publication, [], 'db_edgecreator');

$url_gen_edgecreator = 'https://edges.ducksmanager.net/edges';

$numeros= [];

foreach($tranches_pretes_pour_publication as $tranche) {
    $id = $tranche['ID'];
    $pays = $tranche['Pays'];
    $magazine = $tranche['Magazine'];
    $publicationcode = $pays.'/'.$magazine;
    $numero = $tranche['Numero'];

    $contributeurs = DM_Core::$d->requete('
      SELECT ID_Utilisateur, contribution
      FROM tranches_en_cours_contributeurs
      WHERE ID_Modele=?'
    , [$id], 'db_edgecreator');

    $photographes = [];
    $createurs = [];

    array_filter($contributeurs, function($contributeur) use(&$photographes, &$createurs) {
        switch($contributeur['contribution']) {
            case 'photographe':
                $photographes[] = $contributeur['ID_Utilisateur'];
            break;
            case 'createur':
                $createurs[] = $contributeur['ID_Utilisateur'];
            break;
        }
    });

    $valeurs= [];
    $valeurs['publicationcode']=$publicationcode;
    $valeurs['issuenumber']=$numero;

    $chemin = $pays .'/gen/'. $magazine .'.'. $numero .'.png';

    $url = $url_gen_edgecreator.'/'.$chemin;

    if (isset($_GET['publier'])) {
        $requete="
          INSERT INTO tranches_pretes (".implode(',',array_keys($valeurs)).")
          VALUES ('".implode($valeurs, "', '")."')";
        DM_Core::$d->requete($requete);

        foreach($photographes as $id_utilisateur) {
            $requete="INSERT INTO tranches_pretes_contributeurs(publicationcode, issuenumber, contributeur, contribution) 
                      VALUES ('$publicationcode','$numero',$id_utilisateur,'photographe')";
            DM_Core::$d->requete($requete);
        }

        foreach($createurs as $id_utilisateur) {
            $requete="INSERT INTO tranches_pretes_contributeurs(publicationcode, issuenumber, contributeur, contribution) 
                      VALUES ('$publicationcode','$numero',$id_utilisateur,'createur')";
            DM_Core::$d->requete($requete);
        }

        copy($url, $chemin);

        DmClient::get_service_results_for_ec('POST', "/edgecreator/model/v2/$id/readytopublish/0");

    }
    $numeros[] = [
        'publicationcode' => $publicationcode,
        'numero' => $numero,
        'url' => $url,
        'photographes' => $photographes,
        'createurs' => $createurs
    ];
}

echo '<pre>'.json_encode($numeros, JSON_PRETTY_PRINT).'</pre>';

foreach ($numeros as $numero) {
    ?><img src="<?=$numero['url']?>" /><?php
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
