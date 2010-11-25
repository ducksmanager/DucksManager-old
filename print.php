<?php header('Content-Type: text/html; charset=utf-8');
require_once('DucksManager_Core.class.php');
if (!isset($_SESSION['user'])) {
    echo IDENTIFICATION_OBLIGATOIRE;
    exit(0);
}
require_once('Liste.class.php');
require_once('JS.class.php');
require_once('Affichage.class.php');


DM_Core::$d->requete_select('SELECT DISTINCT Pays,Magazine,Numero,Etat,ID_Acquisition,AV,ID_Utilisateur FROM numeros WHERE (ID_Utilisateur=1) ORDER BY Pays, Magazine, Numero');

$id_user = DM_Core::$d->user_to_id($_SESSION['user']);
global $l;
$l = DM_Core::$d->toList($id_user);

if (isset($_POST['magazine'])) {
    afficher_boite($_POST['pays'], $_POST['magazine']);
    exit(0);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>DucksManager : <?= IMPRESSION_COLLECTION ?></title>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <!--[if IE]>
              <style type="text/css" media="all">@import "fix-ie.css";</style>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="scriptaculous.css" />
        <link rel="icon" type="image/png" href="favicon.png" />
        <link rel="stylesheet" type="text/css" href="csstabs.css">
        <link rel="stylesheet" href="protomenu.css" type="text/css" media="screen">
        <?php
        new JS('prototype.js');
        new JS('js/l10n.js');
        new JS('js/json/json2.js');
        new JS('js/menu_contextuel.js');
        new JS('js/scriptaculous/src/scriptaculous.js');
        new JS('js/my_scriptaculous.js');
        new JS('js/ajax.js'); ?>

    </head>
    <body onload="implement_dragsanddrops()" id="body">
        <?php
        $requete_est_init='SELECT Count(Position_Liste) as cpt_boites FROM parametres_listes WHERE `ID_Utilisateur`='.$id_user;
        $resultat_est_init=DM_Core::$d->requete_select($requete_est_init);
        $est_init=1;//$resultat_est_init[0]['cpt_boites']==0;
        if ($est_init) {
            $i=0;
            foreach ($l->collection as $pays => $magazines) {
                foreach ($magazines as $nom_magazine => $magazine) {
                    $type_liste_auto=$l->get_liste_auto($pays,$nom_magazine);
                    $type_liste = 'dmspiral';
                    if (file_exists('Listes/Liste.'.$type_liste.'.class.php'))
                        include_once('Listes/Liste.'.$type_liste.'.class.php');
    
                    $o_tmp=new $type_liste;
                    foreach($o_tmp->getListeParametres() as $nom_parametre=>$parametre) {
                        $requete_ajouter_boite='INSERT INTO parametres_listes(`ID_Utilisateur`,`Pays`,`Magazine`,`Type_Liste`,`Position_Liste`,`Parametre`,`Valeur`) VALUES '
                                              .'('.$id_user.',\''.$pays.'\',\''.$nom_magazine.'\',\''.$type_liste.'\','.$i.',\''.$nom_parametre.'\',\''.$parametre->valeur.'\')';
                        DM_Core::$d->requete($requete_ajouter_boite);
                    }
                    afficher_boite($pays,$nom_magazine,$type_liste);
                    $i++;
                }
            }
        }
?>
        <a id="lien_cacher_aide" class="toggle_info" href="javascript:void(0)" onclick="toggle_aide()">&lt;&lt; <span name="cacher_aide"></span></a>
        <a id="lien_afficher_aide" class="toggle_info cache" href="javascript:void(0)" onclick="toggle_aide()"><span name="afficher_aide"></span> &gt;&gt;</a>
        <div id="info"><?php
        $onglets=array(
            PRESENTATION=>array('presentation',PRESENTATION),
            INDEX_AIDE=>array('index_aide',INDEX_AIDE),
            PARAMETRES=>array('parametres',PARAMETRES));
        $onglet='presentation';
        Affichage::onglets($onglet,$onglets,'onglet_aide','?');
        ?>
            <div id="titre_info" style="text-align:center"><?=IMPRIMER_AIDE_TITRE?></div>
            <hr />
            <div id="contenu_info">
                <?=IMPRIMER_AIDE_1?>
                <br /><br />
                <?=IMPRIMER_AIDE_2?>
                <br /><br />
                <?=IMPRIMER_AIDE_3?>
                <br /><br />
                <?=IMPRIMER_AIDE_4?>
                <br /><br /><br />
                <?=IMPRIMER_AIDE_5?>
                <br /><br />
                <button onclick="imprimer()"><?=IMPRIMER_COLLECTION?></button>
            </div>
        </div>
    </body>
</html>

<?php
function afficher_boite($pays,$nom_magazine,$type_liste) {
    global $l;
    list($nom_complet_pays, $nom_complet_magazine) = DM_Core::$d->get_nom_complet_magazine($pays, $nom_magazine);
    $sous_liste = new Liste();
    $sous_liste = $l->sous_liste($pays, $nom_magazine);
    ?><div class="draggable_box widget" id="box_<?= $pays ?>_<?= $nom_magazine ?>">
        <table>
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td valign="top" class="titre_magazine" name="<?= $nom_complet_magazine ?>">
                                <?= $nom_complet_magazine ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr id="<?= $pays ?>_<?= $nom_magazine ?>">
                <td id="<?= $pays ?>_<?= $nom_magazine ?>_contenu" name="<?= $type_liste ?>" class="contenu_liste">
<?= $sous_liste->afficher($type_liste) ?>
                </td>
                <td valign="top">
                </td>
            </tr>
        </table>
    </div>
    <div class="espacement" id="espacement_<?=$pays?>_<?=$nom_magazine?>"><br /><br /></div>
<?php
}