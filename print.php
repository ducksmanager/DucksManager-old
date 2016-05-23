<?php header('Content-Type: text/html; charset=utf-8'); 
require_once('DucksManager_Core.class.php');
require_once('Liste.class.php');
require_once('Affichage.class.php');
require_once('Listes/Format_liste.php');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>DucksManager : <?= IMPRESSION_COLLECTION ?></title>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <!--[if IE]>
              <style type="text/css" media="all">@import "fix-ie.css";</style>
        <![endif]-->
        <style type="text/css">
            .draggable_box {
                margin-bottom:<?=$parametres_generaux->parametres->espacement_boites->valeur?>px;
                border-color:rgb(<?=$parametres_generaux->parametres->bordure_boites_r->valeur?>,<?=$parametres_generaux->parametres->bordure_boites_v->valeur?>,<?=$parametres_generaux->parametres->bordure_boites_b->valeur?>);
            }
        </style>
        <link rel="stylesheet" type="text/css" href="scriptaculous.css" />
        <link rel="icon" type="image/png" href="favicon.png" />
        <link rel="stylesheet" type="text/css" href="csstabs.css">
        <link rel="stylesheet" type="text/css" href="csstabs.css">
        <link rel="stylesheet" href="protomenu.css" type="text/css" media="screen">
        <script type="text/javascript" src="prototype-1.7.3.js"></script>
        <script type="text/javascript" src="js/l10n.js"></script>
        <script type="text/javascript" src="js/json/json2.js"></script>
        <script type="text/javascript" src="js/menu_contextuel.js"></script>
        <script type="text/javascript" src="js/scriptaculous/src/scriptaculous.js"></script>
        <script type="text/javascript" src="js/my_scriptaculous.js"></script>
        <script type="text/javascript" src="js/ajax.js"></script>
    </head>
    <body onload="implement_dragsanddrops()" id="body">
<?php
if (!isset($_SESSION['user'])) {
    echo IDENTIFICATION_OBLIGATOIRE;
    ?><br /><a href="?action=open"><?=CONNEXION_COMPTE?></a><?php
    exit(0);
}
DM_Core::$d->requete_select('SELECT DISTINCT Pays,Magazine,Numero,Etat,ID_Acquisition,AV,ID_Utilisateur FROM numeros WHERE (ID_Utilisateur=1) ORDER BY Pays, Magazine, Numero');

$id_user = DM_Core::$d->user_to_id($_SESSION['user']);
global $l;
$l = DM_Core::$d->toList($id_user);

if (isset($_POST['magazine'])) {
    afficher_boite($_POST['pays'], $_POST['magazine'],$_POST['type_liste']);
    exit(0);
}

$parametres_generaux=new parametres_generaux();
$requete_est_init='SELECT Count(Position_Liste) as cpt_boites FROM parametres_listes WHERE `ID_Utilisateur`='.$id_user;
$resultat_est_init=DM_Core::$d->requete_select($requete_est_init);
$est_init=$resultat_est_init[0]['cpt_boites']==0;
if ($est_init) {
    foreach($parametres_generaux->getListeParametresModifiables() as $nom_parametre=>$parametre) {
        $requete_ajouter_boite='INSERT INTO parametres_listes(`ID_Utilisateur`,`Pays`,`Magazine`,`Type_Liste`,`Position_Liste`,`Parametre`,`Valeur`) VALUES '
                              .'('.$id_user.',NULL,NULL,NULL,-1,\''.$nom_parametre.'\',\''.$parametre->valeur_defaut.'\')';
        DM_Core::$d->requete($requete_ajouter_boite);
    }
}
else {
    foreach($parametres_generaux->getListeParametresModifiables() as $nom_parametre=>$parametre) {
        $requete_get_valeur='SELECT Valeur FROM parametres_listes WHERE ID_Utilisateur='.$id_user.' AND Position_Liste = -1 AND Parametre = \''.$nom_parametre.'\'';
        $resultat_get_valeur=DM_Core::$d->requete_select($requete_get_valeur);
        if (count($resultat_get_valeur) > 0)
            $parametres_generaux->parametres->$nom_parametre->valeur=$resultat_get_valeur[0]['Valeur'];
    }
}
?>
        <div style="text-align:center"><?=strtoupper(IMPRESSION_COLLECTION.' DucksManager')?></div>
        <hr />
        <div id="container">
            <?php
            $i=0;
            foreach ($l->collection as $pays => $magazines) {
                foreach ($magazines as $nom_magazine => $magazine) {
                    $requete_get_boite='SELECT Pays,Magazine,Type_Liste,Position_Liste FROM parametres_listes WHERE (ID_Utilisateur='.$id_user.' AND Pays = \''.$pays.'\' AND Magazine = \''.$nom_magazine.'\') GROUP BY Position_Liste ORDER BY Position_Liste';
                    $resultat_get_boite=DM_Core::$d->requete_select($requete_get_boite);
                    $est_init_magazine=count($resultat_get_boite) == 0;
                    if ($est_init_magazine) {
                        $type_liste = 'dmspiral';
                        Liste::init_parametres_boite($pays,$nom_magazine,$type_liste,$i);
                    }
                    $resultat_get_boite=DM_Core::$d->requete_select($requete_get_boite);

                    $proprietes_boite=$resultat_get_boite[0];
                    $type_liste = $proprietes_boite['Type_Liste'];
                    if (file_exists('Listes/Liste.'.$type_liste.'.class.php'))
                        include_once('Listes/Liste.'.$type_liste.'.class.php');
                    $o_tmp=new $type_liste;
                    foreach($o_tmp->getListeParametresModifiables() as $nom_parametre=>$parametre) {
                        $requete_get_parametre='SELECT Valeur FROM parametres_listes WHERE Position_Liste='.$proprietes_boite['Position_Liste'].' AND Parametre = \''.$nom_parametre.'\' AND ID_Utilisateur='.$id_user;
                        $resultat_get_parametre=DM_Core::$d->requete_select($requete_get_parametre);
                        if (count($resultat_get_parametre) > 0)
                            $o_tmp->parametres->$nom_parametre->valeur=$resultat_get_parametre[0]['Valeur'];
                    }
                    afficher_boite($pays,$nom_magazine,$type_liste,$o_tmp->parametres);
                    $i++;
                }
            }
            ?>
            <a id="lien_cacher_aide" class="toggle_info" href="javascript:void(0)" onclick="toggle_aide()">&lt;&lt; <span name="cacher_aide"></span></a>
            <a id="lien_afficher_aide" class="toggle_info cache" href="javascript:void(0)" onclick="toggle_aide()"><span name="afficher_aide"></span> &gt;&gt;</a>
            <div id="info"><?php
            $onglets= [
                PRESENTATION=> ['presentation',PRESENTATION],
                INDEX_AIDE=> ['index_aide',INDEX_AIDE],
                PARAMETRES=> ['parametres',PARAMETRES]];
            Affichage::onglets('parametres',$onglets,'onglet_aide','?');
            ?>
                <div id="contenu_presentation" class="contenu" style="display:none">
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
                        <br /><br />
                        <?=IMPRIMER_AIDE_5?>
                    </div>
                </div>
                <div class="contenu" id="contenu_index_aide" style="display:none">
                    <div id="titre_index_aide">Aide</div>
                </div>
                <div class="contenu" id="contenu_parametres" style="display:block">
                    <?php
                    $onglets= [
                        GENERAL=> ['general',GENERAL],
                        BOITE_SELECTIONNEE=> ['boite_selectionnee',BOITE_SELECTIONNEE]];
                    Affichage::onglets('general',$onglets,'onglet_type_param','?');
                    ?>
                    <div id="contenu_general" style="display:block;visibility:hidden">
                        <table style="width:100%">
                        <?php
                        foreach($parametres_generaux->getListeParametresModifiables() as $nom_parametre=>$parametre) { 
                            ?>
                            <tr><td colspan="2"><?=$parametre->texte?></td></tr>
                            <tr class="details_parametre" id="<?=$nom_parametre?>">
                                <td>    
                                    <div class="slider">
                                        <div class="handle" style="z-index:2"></div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <input class="valeur_courante" type="text" value="<?=$parametre->valeur?>" readonly="readonly" />
                                        <input class="min cache" type="text" value="<?=$parametre->min?>" readonly="readonly" />
                                        <input class="max cache" type="text" value="<?=$parametre->max?>" readonly="readonly" />
                                        <input class="valeurs cache" type="text" value="" readonly="readonly" />
                                        <input class="valeur_defaut cache" type="text" value="<?=$parametre->valeur_defaut?>" readonly="readonly" />
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </table>
                    </div>
                    <div class="contenu" id="contenu_boite_selectionnee" style="display:none;margin-top: 25px">
                    </div>
                </div>
            </div>
        </div>
        <div id="infos_sv"></div>
        <div id="section_imprimer">
            <button onclick="imprimer()"><?=IMPRIMER_COLLECTION?></button>
        </div>
    </body>
</html>

<?php
function afficher_boite($pays,$nom_magazine,$type_liste,$parametres=null) {
    $nom_complet_magazine = Inducks::get_nom_complet_magazine($pays, $nom_magazine);
    ?><span class="draggable_box widget" id="box_<?= $pays ?>_<?= $nom_magazine ?>">
        <div class="parametres_box cache"><?=json_encode($parametres)?></div>
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
                <td id="<?= $pays ?>_<?= $nom_magazine ?>_contenu" title="<?= $type_liste ?>" class="contenu_liste">
                    <?=CHARGEMENT?>...
                </td>
                <td valign="top">
                </td>
            </tr>
        </table>
    </span>
<?php
}