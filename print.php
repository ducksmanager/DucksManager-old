<?php header('Content-Type: text/html; charset=utf-8');
session_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
include_once ('locales/lang.php');
if (!isset($_SESSION['user'])) {
    echo IDENTIFICATION_OBLIGATOIRE;
    exit(0);
}
require_once('Liste.class.php');
require_once('JS.class.php');
require_once('Database.class.php');

global $d;
$d = new Database();
$d->requete('SET NAMES UTF8');
$d->requete_select('SELECT DISTINCT Pays,Magazine,Numero,Etat,ID_Acquisition,AV,ID_Utilisateur FROM numeros WHERE (ID_Utilisateur=1) ORDER BY Pays, Magazine, Numero');
if (!$d) {
    echo PROBLEME_BD;
    exit(-1);
}
$id_user = $d->user_to_id($_SESSION['user']);
global $l;
$l = $d->toList($id_user);

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
        <link rel="stylesheet" href="protomenu.css" type="text/css" media="screen">
        <?php
        new JS('prototype.js');
        new JS('js/l10n.js');
        new JS('js/menu_contextuel.js');
        new JS('js/scriptaculous/src/scriptaculous.js');
        new JS('js/my_scriptaculous.js');
        new JS('js/ajax.js'); ?>

    </head>
    <body onload="implement_dragsanddrops()" id="body">
        <?php
        foreach ($l->collection as $pays => $magazines) {
            foreach ($magazines as $nom_magazine => $magazine) {
                afficher_boite($pays,$nom_magazine);
            }
        }
?>
        <a id="lien_cacher_aide" class="toggle_info" href="javascript:void(0)" onclick="toggle_aide()">&lt;&lt; <span name="cacher_aide"></span></a>
        <a id="lien_afficher_aide" class="toggle_info cache" href="javascript:void(0)" onclick="toggle_aide()"><span name="afficher_aide"></span> &gt;&gt;</a>
        <div id="info">
            <div style="text-align:center"><?=IMPRIMER_AIDE_TITRE?></div>
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
function afficher_boite($pays,$nom_magazine) {
    global $d;
    global $l;
    list($nom_complet_pays, $nom_complet_magazine) = $d->get_nom_complet_magazine($pays, $nom_magazine);
    $type_liste_affichage = 'CollecTable';
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
                <td id="<?= $pays ?>_<?= $nom_magazine ?>_contenu" name="<?= $type_liste_affichage ?>" class="contenu_liste">
<?= $sous_liste->afficher($type_liste_affichage) ?>
                </td>
                <td valign="top">
                </td>
            </tr>
        </table>
    </div>
    <div class="espacement" id="espacement_<?=$pays?>_<?=$nom_magazine?>"><br /><br /></div>
<?php
}