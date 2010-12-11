<?php header('Content-Type: text/html; charset=utf-8');
require_once('_priv/Admin.priv.class.php');
require_once('travaux.php');
require_once('DucksManager_Core.class.php');
require_once('Liste.class.php');
require_once('JS.class.php');
require_once('Menu.class.php');
require_once('Affichage.class.php');
require_once('Inducks.class.php');
require_once('Util.class.php');

$action=isset($_GET['action'])?$_GET['action']:null;
if (defined('TITRE_PAGE_'.strtoupper($action)))
    $titre=constant('TITRE_PAGE_'.strtoupper($action));
else
    $titre=constant('TITRE_PAGE_ACCUEIL');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <?php
            if (isset($_GET['debug']) || (!is_null($action) && $action=='bibliotheque')) {
                ?><meta http-equiv="Pragma" content="no-cache" /><?php
            }
        ?>
        <title><?php echo TITRE.' - '.$titre;?></title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <!--[if IE]>
              <style type="text/css" media="all">@import "fix-ie.css";</style>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="scriptaculous.css">
        <link rel="stylesheet" type="text/css" href="autocompleter.css">
        <link rel="stylesheet" type="text/css" href="csstabs.css">
        <link rel="stylesheet" type="text/css" href="bibliotheque.css">
        <link rel="stylesheet" href="protomenu.css" type="text/css" media="screen">
        <link rel="icon" type="image/png" href="favicon.png">
        <?php include_once('_priv/Database.priv.class.php');
        if (!isLocalHost()) {?>
            <!-- Piwik -->
            <script type="text/javascript">
            var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.ducksmanager.net/piwik/" : "http://www.ducksmanager.net/piwik/");
            document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
            </script>
            <script type="text/javascript">
            try {
            var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
            piwikTracker.trackPageView();
            piwikTracker.enableLinkTracking();
            } catch( err ) {}
            </script>
            <!-- End Piwik Tag -->
        <?php
        }?>
        <script type="text/javascript">
            var debug=<?=isset($_GET['debug']) ? 'true':'false'?>;
        </script><?php
        new JS('prototype.js');
        new JS('js/scriptaculous/src/scriptaculous.js');
        new JS('js/my_scriptaculous.js');
        new JS('js/l10n.js');
        new JS('js/ajax.js');
        if (!is_null($action)) {
            new JS('js/sel_num.js');
            switch($_GET['action']) {
                case 'gerer':
                    new JS('js/menu_contextuel.js');
                break;  
                case 'bibliotheque':
                    new JS('js/edges.js');
                break; 
                case 'stats':
                    switch($_GET['onglet']) {
                        case 'possessions':
                            new JS('js/chargement.js');
                            new JS('js/classement_histogramme.js');
                            ?>
                            <script type="text/javascript" src="js/json/json2.js"></script>
                            <script type="text/javascript" src="js/swfobject.js"></script>
                            <?php
                        break;
                    }
                break;
            }
            new JS('js/selection_menu.js');
            new JS('js/bouquineries.js');
            new JS('js/divers.js');
        }
        ?>
    </head>

    <?php
    $texte_debut='';
    if ($action=='open'&& isset($_POST['user'])) {
        if (!DM_Core::$d->user_connects($_POST['user'],$_POST['pass']))
            $texte_debut.= 'Identifiants invalides!<br /><br />';
        else {
            creer_id_session($_POST['user'],$_POST['pass']);
        }
    }
    else {
        if (isset($_COOKIE['user']) && isset($_COOKIE['pass'])) {
            if (!DM_Core::$d->user_connects($_COOKIE['user'],$_COOKIE['pass'])) {
                $_SESSION['user']=$_COOKIE['user'];
                setCookie('user',$_COOKIE['user'],time()+3600); // On met les 2 cookies à jour à chaque rafraichissement
                setCookie('pass',sha1($_COOKIE['pass']),time()+3600);
            }
        }
    }
    ?>
    <body id="body" style="margin:0" onload="
    <?php
    switch($action) {
        case 'open':
            echo 'defiler_log(\'DucksManager\');';
            break;
        case 'bibliotheque':
            if (!isset($_GET['onglet']) || $_GET['onglet']=='affichage') {
                if (Util::getBrowser()!=='MSIE') {
                    $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                    $textures=array();
                    for ($i=1;$i<=2;$i++) {
                        $requete_textures='SELECT Bibliotheque_Texture'.$i.', Bibliotheque_Sous_Texture'.$i.' FROM users WHERE ID LIKE \''.$id_user.'\'';
                        $resultat_textures=DM_Core::$d->requete_select($requete_textures);
                        $textures[]=$resultat_textures[0]['Bibliotheque_Texture'.$i];
                        $textures[]=$resultat_textures[0]['Bibliotheque_Sous_Texture'.$i];
                    }
                    $requete_grossissement='SELECT Bibliotheque_Grossissement FROM users WHERE ID LIKE \''.$id_user.'\'';
                    $resultat_grossissement=DM_Core::$d->requete_select($requete_grossissement);
                    $grossissement=$resultat_grossissement[0]['Bibliotheque_Grossissement'];
                    $regen=isset($_GET['regen']) ? 1 : 0;
                    echo 'charger_bibliotheque(\''.$textures[0].'\',\''.$textures[1].'\', \''.$textures[2].'\',\''.$textures[3].'\', \''.$grossissement.'\','.$regen.');';
                }
            }
            elseif (isset($_GET['onglet']) && $_GET['onglet']=='options') {
                echo 'initTextures();';
            }
        break;
        case 'gerer':
            echo 'defiler_log(\'DucksManager\');';
            if (isset($_GET['onglet_magazine'])) {
                $onglet_magazine=$_GET['onglet_magazine'];
                if ($onglet_magazine=='new') {
                    if (isset($_POST['magazine'])) {
                        $pays=$_POST['pays'];
                        $magazine=$_POST['magazine'];
                    }
                    echo 'initPays();';
                }
                else {
                    list($pays,$magazine)=explode('/',$onglet_magazine);
                    echo 'montrer_magazines(\''.$pays.'\');';
                }
                if (isset($magazine)) {
                    echo 'afficher_numeros(\''.$pays.'\',\''.$magazine.'\');';
                }
            }
            break;
        case 'print_now':
            echo 'implement_drags();';
            echo 'observe_options_clicks();';
            break;
        case 'stats':
            if (isset($_GET['onglet']) && $_GET['onglet']=='auteurs') {
                echo 'init_autocompleter_auteurs();';
            }
            break;
        case 'agrandir':
            if (isset($_GET['onglet']) && $_GET['onglet']=='bouquineries') {
                //echo 'initPays();';
                //echo 'select_etats();';
            }
            if (isset($_GET['onglet']) && $_GET['onglet']=='auteurs_favoris') {
                echo 'init_autocompleter_auteurs();';
                echo 'init_notations();';
            }
            break;
        default:echo 'defiler_log(\'DucksManager\');';
    }
    ?>
    ">
    <table
        style="text-align: left; color: white; background-color: rgb(61, 75, 95); width: 100%; height: 100%;border:0"
        cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td align="center" style="height:45px;padding-left:3px;background-color:rgb(61, 75, 95);width:160px;">
                    <table width="100%" style="width:100%">
                        <tr>
                            <td align="center" id="log" style="height:32px" >&nbsp;</td>
                            <td align="center" id="loading" style="width:40px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="width:120px">
                                <div style="padding-left:5px;border:2px solid rgb(255, 98, 98);" id="connected">
                                    <img id="light" 
                                    <?php
                                    if (isset($_SESSION['user']) &&!($action=='logout')) {?>
                                        src="vert.png" alt="O" />&nbsp;<span id="texte_connecte"><?=CONNECTE_EN_TANT_QUE.$_SESSION['user']?></span>
                                    <?php } else {?>
                                        src="rouge.png" alt="X" />&nbsp;<span id="texte_connecte"><a href="?action=open"><?=NON_CONNECTE?></a></span><br /><br />
                                    <?php }?>
                                </div>
                            </td></tr>
                    </table>
                </td>
                <td style="background-color:rgb(200, 137, 100);height: 30px; text-align: center;"><!--
                <span style="font-weight: bold;"><img src="favicon.png" />&nbsp;DucksManager 3&nbsp;<img src="favicon_inv.png" /></span>!-->
                    <img src="images/logo2.png" alt="DucksManager"/></td>
                <td>
                </td>
                <td valign="middle" align="right" style="background-color:rgb(200, 137, 100);height: 79px; width: 98px;">
                    <?php if (!(Util::isLocalHost())) { ?>
                        <script type="text/javascript" src="jw/swfobject.js"></script>
                        <object id="player" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" name="player" width="79" height="98">
                            <param name="movie" value="jw/player.swf" />
                            <param name="allowfullscreen" value="false" />
                            <param name="allowscriptaccess" value="always" />
                            <param name="flashvars" value="file=../morph/morph.swf.flv&amp;controlbar=none&amp;autostart=true&amp;image=morph/rfl.jpg" />
                            <object type="application/x-shockwave-flash" data="jw/player.swf" width="79" height="98">
                                <param name="movie" value="jw/player.swf" />
                                <param name="allowfullscreen" value="true" />

                                <param name="allowscriptaccess" value="always" />
                                <param name="flashvars" value="file=../morph/morph.swf.flv&amp;autostart=true&amp;controlbar=none&amp;image=morph/rfl.jpg" />
                                <p><a href="http://get.adobe.com/flashplayer"><?=TELECHARGER_FLASH?></a> <?=POUR_VOIR_LA_VIDEO?>.</p>
                            </object>
                        </object>
                    <?php } ?>
                </td>
            </tr>
            <tr style="height:3px;background-color:black;"><td colspan="5"></td></tr>
            <tr style="height:100%">
                <td style="height: 441px; vertical-align: top; width: 242px; background-color: rgb(200, 137, 100);">
                    <table style="height:100%; width:100%" cellspacing="0"><tbody>
                            <tr>
                                <td valign="top" style="padding:5px;">
                                    <b><a href="?"><?=ACCUEIL?></a></b><br /><br />
                                    <?php
                                    $beta_user=DM_Core::$d->user_is_beta();
                                    foreach($menus as $i=>$menu) {
                                        ?>
                                        <span style="font-weight: bold; text-decoration: underline;"><?=$menu->nom?></span><br />
                                        <?php
                                        foreach($menu->items as $j=>$item) {
                                            if ($item->est_prive=='no'
                                            || ($item->est_prive=='always' && isset($_SESSION['user']) &&!($action=='logout'))
                                            || ($item->est_prive=='never'  &&!(isset($_SESSION['user']) &&!($action=='logout')))) {
                                                if ($item->beta && !$beta_user)
                                                    continue;
                                                ?>
                                                <a href="?action=<?=$item->nom?>"><?=$item->texte?>
                                                <?php
                                                if ($item->beta && $beta_user) {
                                                    ?><span class="beta"><?=BETA?></span>
                                                    <?php
                                                }?>
                                                </a><br>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <br />
                                        <?php
                                    }
                                    ?>
                                    <br/>
                                </td>
                            </tr></tbody>
                    </table>
                </td>
                <td colspan="2" style="padding-left:5px;vertical-align: top;background-color:rgb(61, 75, 95);">
                    <?php if (!isset($_GET['action'])) {
                        ?>
                        <h3><?=BIENVENUE?></h3>
                        <?php
                    }
                    ?>
                    <div id="contenu">

                        <?php
                        echo $texte_debut;
                        foreach($menus as $i=>$menu) {
                            foreach($menu->items as $j=>$item) {
                                if ($item->nom==$action) {
                                    /*if (isset($infos_sous_item['coa_related'])) {
                                        require_once('Util.class.php');
                                        $contenu_page=Util::get_page('http://coa.inducks.org/maccount.php');
                                        if (!(strpos($contenu_page,'is experiencing technical difficulties') === false)) {
                                            ?>
                                            <span style="color:red;"><?=PHRASE_MAINTENANCE_INDUCKS1?>
                                                    <a href="coa.inducks.org">COA</a>,
                                                    <?=PHRASE_MAINTENANCE_INDUCKS2?><br />
                                                    <?=PHRASE_MAINTENANCE_INDUCKS3?></span><br /><br />
                                            <?php
                                        }
                                    }*/
                                    if ($item->est_prive=='always' && !isset($_SESSION['user'])) {
                                        echo IDENTIFICATION_OBLIGATOIRE.'<br />';
                                        echo COMMENT_S_IDENTIFIER;
                                        $action='aucune';
                                    }
                                }
                            }
                        }                   
                        switch($action) {
                            case 'inducks':
                                if (isset($_POST['rawData'])) {
                                    if (isset($_POST['valider_importer'])) {
                                        if (isset($_SESSION['user'])) {
                                            $ajouter_numeros_inducks=isset($_POST['ajouter_numeros']);
                                            $supprimer_numeros_inducks=isset($_POST['supprimer_numeros']);
                                            $l=new Liste($_POST['rawData']);
                                            $l->lire();
                                            $l->synchro_to_database(DM_Core::$d,$ajouter_numeros_inducks,$supprimer_numeros_inducks);
                                        }
                                        else {
                                            if ($_POST['valider_importer']=='Oui') {
                                                echo IMPORT_DANS_NOUVEAU_COMPTE;
                                                formulaire_inscription();
                                            }
                                            else {
                                                header('index.php');
                                            }
                                        }
                                        
                                    }
                                    else {
                                        $rawdata_valide=(Inducks::liste_numeros_valide($_POST['rawData']));
                                        if ($rawdata_valide) {
                                            list($est_succes,$ajouts,$suppressions)=Liste::import($_POST['rawData']);
                                            if ($est_succes) {
                                                ?><br /><br />
                                                <form method="post" action="?action=inducks">
                                                    <input type="hidden" name="rawData" value="<?=$_POST['rawData']?>" />
                                                <?php
                                                if (isset($_SESSION['user'])) {
                                                    ?><?=QUESTION_EXECUTER_OPS_INDUCKS?><br />
                                                    <?php
                                                    if ($ajouts > 0) {
                                                        ?><input type="checkbox" checked="checked" name="ajouter_numeros" /><?=AJOUTER_NUMEROS_INDUCKS?><br /><?php
                                                    }
                                                    if ($suppressions > 0) {
                                                        ?><input type="checkbox" name="supprimer_numeros" /><?=SUPPRIMER_NUMEROS_NON_INDUCKS?><br /><?php
                                                    }?>
                                                    <input type="submit" name="valider_importer" value="<?=VALIDER?>" />&nbsp;

                                                    <?php
                                                }
                                                else {
                                                    ?><?=QUESTION_IMPORTER_INDUCKS?><br />
                                                    <input type="submit" name="valider_importer" value="<?=OUI?>" />&nbsp;
                                                    <input type="submit" name="valider_importer" value="<?=NON?>" />
                                                <?php }?>
                                                </form>
                                                <?php
                                            }
                                        }
                                        else {
                                            echo ERREUR_RAWDATA_INVALIDE;
                                        }
                                    }
                                }
                                if (!isset($_POST['rawData']) || isset($rawdata_valide) && !$rawdata_valide) {
                                    ?><table border="0" style="width:90%;height:70%" cellspacing="5">
                                        <tr>
                                            <td>
                                                <iframe src="http://coa.inducks.org/collection.php" style="width:100%;height:400px"></iframe>
                                            </td>
                                            <td>
                                                <h2><?=INSTRUCTIONS_IMPORT_INDUCKS_TITRE?></h2>
                                                <ul>
                                                    <li><?=INSTRUCTIONS_IMPORT_INDUCKS_1?></li>
                                                    <li><?=INSTRUCTIONS_IMPORT_INDUCKS_2?></li>
                                                    <li><?=INSTRUCTIONS_IMPORT_INDUCKS_3?></li>
                                                    <li><?=INSTRUCTIONS_IMPORT_INDUCKS_4?></li>
                                                    <li><?=INSTRUCTIONS_IMPORT_INDUCKS_5?></li>
                                                </ul><br />
                                                <form method="post" action="?action=inducks">
                                                    <textarea name="rawData" rows="10" cols="40"></textarea>
                                                    <br />
                                                    <input type="submit" value="<?=IMPORTER?>" />
                                                </form>
                                            </td>
                                         </tr>
                                    </table>
                                <?php
                                }
                            break;
                            case 'new':
                                formulaire_inscription();
                                break;
                            case 'open':
                                if (!isset($_SESSION['user'])) {
                                    ?>
                                    <?=IDENTIFIEZ_VOUS?><br /><br />
                                    <form method="post" action="index.php?action=open">
                                        <table border="0"><tr><td><?=NOM_UTILISATEUR?> :</td><td><input type="text" name="user" /></td></tr>
                                        <tr><td><?=MOT_DE_PASSE?> :</td><td><input type="password" name="pass" /></td></tr>
                                        <tr><td align="center" colspan="2"><input type="submit" value="<?=CONNEXION?>"/></td></tr></table></form>
                                    
                                    <?php
                                }
                                break;
                            case 'logout':
                                session_destroy();
                                session_unset();
                                setCookie('user','',time()-3600);
                                setCookie('pass','',time()-3600);
                                echo DECONNEXION_OK;
                                break;
                            case 'bibliotheque':
                                ?>
                                <h2><?=BIBLIOTHEQUE_COURT?></h2><br /><br />
                                <?php
                                $onglets=array(
                                        BIBLIOTHEQUE_COURT=>array('affichage',BIBLIOTHEQUE),
                                        BIBLIOTHEQUE_OPTIONS_COURT=>array('options',BIBLIOTHEQUE_OPTIONS),
                                        BIBLIOTHEQUE_PARTICIPER_COURT=>array('participer',BIBLIOTHEQUE_PARTICIPER),
                                        BIBLIOTHEQUE_CONTRIBUTEURS_COURT=>array('contributeurs',BIBLIOTHEQUE_CONTRIBUTEURS));
                                if (!isset($_GET['onglet']))
                                    $onglet='affichage';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet,$onglets,'onglet','?action=bibliotheque');
                                switch($onglet) {
                                    case 'affichage':
                                        if (Util::getBrowser()==='MSIE') {
                                            echo IE_NON_SUPPORTE;
                                        }
                                        else {
                                            ?>
                                            <span id="chargement_bibliotheque_termine"><?=CHARGEMENT?>..</span>.<br />
                                            <div id="barre_pct_bibliotheque" style="border: 1px solid white; width: 200px;">
                                                <div id="pct_bibliotheque" style="width: 0%; background-color: red;">&nbsp;</div>
                                            </div>
                                            <span id="pcent_visible"></span>
                                            <span id="pourcentage_collection_visible"></span>
                                            <?php if (DM_Core::$d->user_is_beta()) { ?>
                                                <br />
                                                <br />
                                                <div id="recherche_bibliotheque">
                                                    <?=RECHERCHER_BIBLIOTHEQUE?><br />
                                                    <input type="text" style="width:300px" name="" />
                                                    <button style="width: 30px;">OK</button>
                                                </div>
                                            <?php } ?>
                                            <br /><br />
                                            <div id="bibliotheque" style="width:100%;height:100%"></div>
                                            <?php
                                        }
                                    break;
                                    case 'options':
                                        require_once('Edge.class.php');
                                        $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                        if (isset($_POST['texture1'])) {
                                            for ($i=1;$i<=2;$i++) {
                                                $requete_update_texture='UPDATE users SET Bibliotheque_Texture'.$i.'=\''.$_POST['texture'.$i].'\' WHERE id='.$id_user;
                                                DM_Core::$d->requete($requete_update_texture);
                                                $requete_update_sous_texture='UPDATE users SET Bibliotheque_Sous_Texture'.$i.'=\''.$_POST['sous_texture'.$i].'\' WHERE id='.$id_user;
                                                DM_Core::$d->requete($requete_update_sous_texture);
                                            }
                                            /*if (!is_numeric($_POST['grossissement']))
                                                $_POST['grossissement']='taille_reelle';*/
                                            /*$requete_update_grossissement='UPDATE users SET Bibliotheque_Grossissement=\''.$_POST['grossissement'].'\' WHERE id='.$id_user;
                                            DM_Core::$d->requete($requete_update_grossissement);*/
                                        }
                                        ?><form method="post" action="?action=bibliotheque&amp;onglet=options">
                                            <span style="text-decoration:underline"><?=TEXTURE?> : </span><br />
                                            <select style="width:300px;" id="texture1" name="texture1">
                                                <option id="chargement_sous_texture"><?=CHARGEMENT?>...</option>
                                            </select>
                                            <br /><br />
                                            <span style="text-decoration:underline"><?=SOUS_TEXTURE?> : </span><br />
                                            <select style="width:300px;" id="sous_texture1" name="sous_texture1">
                                                <option id="vide"><?=SELECTIONNER_TEXTURE?></option>
                                            </select>
                                            <br /><br /><br />
                                            <span style="text-decoration:underline"><?=TEXTURE_ETAGERE?> : </span><br />
                                            <select style="width:300px;" id="texture2" name="texture2">
                                                <option id="chargement_sous_texture"><?=CHARGEMENT?>...</option>
                                            </select>
                                            <br /><br />
                                            <span style="text-decoration:underline"><?=SOUS_TEXTURE_ETAGERE?> : </span><br />
                                            <select style="width:300px;" id="sous_texture2" name="sous_texture2">
                                                <option id="vide"><?=SELECTIONNER_TEXTURE?></option>
                                            </select>
                                            <br /><br />
                                            <?php /*
                                            <span style="text-decoration:underline"><?=TAILLE_TRANCHES?> : </span><br />
                                            <select style="width:300px;" id="grossissement" name="grossissement">
                                            <?php
                                            $requete_grossissement='SELECT Bibliotheque_Grossissement FROM users WHERE id='.$id_user;
                                            $resultat_grossissement=DM_Core::$d->requete_select($requete_grossissement);
                                            if (count($resultat_grossissement)==0)
                                                $grossissement=Edge::$grossissement;
                                            else
                                                $grossissement=$resultat_grossissement[0]['Bibliotheque_Grossissement'];
                                            $options_grossissement=array(1,1.5,2);
                                            foreach($options_grossissement as $option) {
                                                ?><option <?php
                                                if ($option==$grossissement || (!is_numeric($option) && $grossissement==='taille_reelle')) {
                                                    ?>selected="selected"<?php
                                                }
                                                ?>
                                                ><?=$option?></option>
                                                <?php
                                            }
                                            ?>
                                            </select>
                                            <br /><br />
                                            <?php */?>
                                            <input type="submit" class="valider" value="<?=VALIDER?>" />
                                        </form>
                                        <?php

                                    break;

                                    case 'participer':
                                        require_once('Edge.class.php');
                                        echo INTRO_PARTICIPER_BIBLIOTHEQUE_1;
                                        ?><br /><br /><?php
                                        $pourcentage_visible=Edge::getPourcentageVisible();
                                        if ($pourcentage_visible==100) {
                                            echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_IMPOSSIBLE;
                                        }
                                        else {
                                            $cryptinstall="captcha/cryptographp.fct.php";
                                            include $cryptinstall;
                                            if (isset($_POST['code'])) {
                                                if (chk_crypt($_POST['code'])) {
                                                    ?>
                                                    <?=MERCI_CONTRIBUTION?><br /><?=EMAIL_ENVOYE;?>
                                                    <?php
                                                    mail('admin@ducksmanager.net', 'Proposition d\'aide pour la bibliothèque',
                                                         $_POST['texte_participation'],'From: '.$_POST['email']);
                                                }
                                                else {
                                                    ?>
                                                    <span style="color: red"><?=ERREUR_CAPTCHA?></span><br /><br />
                                                    <?php
                                                }
                                            }
                                            if (!isset($_POST['code']) || !chk_crypt($_POST['code'])) {
                                                echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_1
                                                    .'<span style="font-weight: bold">'.$pourcentage_visible.'</span>'
                                                    .INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_2;
                                                ?><br /><br /><?php
                                                echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_3;
                                                ?><br /><?php
                                                echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_4;
                                                ?>
                                                <br /><br />
                                                <form action="?session_id=<?=session_id()?>&amp;action=bibliotheque&amp;onglet=participer" method="post">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <?=VOTRE_ADRESSE_EMAIL?> :
                                                            </td>
                                                            <td>
                                                                <input type="text" name="email" />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?=SPECIFIER_NUMEROS_BIBLIOTHEQUE?> :
                                                            </td>
                                                            <td>
                                                                <textarea cols="40" rows="10" name="texte_participation"></textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?=RECOPIER_CODE_SUIVANT?>
                                                            </td>
                                                            <td style="background-color:gray">
                                                                <?php dsp_crypt(0, 1); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?=ICI?> :
                                                            </td>
                                                            <td>
                                                                <input type="text" name="code" />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td>
                                                                <input type="submit" class="valider" value="<?=VALIDER?>" />
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </form>
                                                <?php
                                            }
                                        }
                                        break;
                                        
                                        case 'contributeurs':
                                            $requete_contributeurs='SELECT Nom, Texte FROM bibliotheque_contributeurs';
                                            $contributeurs=DM_Core::$d->requete_select($requete_contributeurs);
                                            ?>
                                                <div style="border:1px solid white">
                                                    <h2 style="text-align:center"><?=INTRO_CONTRIBUTEURS_BIBLIOTHEQUE?></h2>
                                            <?php
                                            foreach($contributeurs as $contributeur) {
                                                ?>
                                                    <span style="font-size:18px;line-height:20px;"><?=$contributeur['Nom']?></span> <?=$contributeur['Texte']?><br />
                                                <?php
                                            }
                                            ?>
                                                </div>
                                            <?php
                                        break;
                                }
                            break;

                            case 'gerer':
                                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                $l=DM_Core::$d->toList($id_user);
                                ?>
                                <h2><?=GESTION_COLLECTION?></h2><br />
                                <?php
                                $onglets=array(
                                        GESTION_NUMEROS_COURT=>array('ajout_suppr',GESTION_NUMEROS),
                                        GESTION_COMPTE_COURT=>array('compte',GESTION_COMPTE));
                                if (!isset($_GET['onglet']))
                                    $onglet='ajout_suppr';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet,$onglets,'onglet','?action=gerer');
                                switch($onglet) {
                                    case 'compte':
                                        if (isset($_POST['submit_options'])) {
                                            echo MODIFICATIONS_OK.'<br />';
                                            DM_Core::$d->requete('UPDATE users SET AccepterPartage='.($_POST['partage']=='on'?'1':'0').', AfficherVideo='.($_POST['video']=='on'?'1':'0').' '
                                                       .'WHERE ID='.$id_user);
                                        }
                                        $resultat_partage=DM_Core::$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        ?>
                                        <form action="?action=gerer&amp;onglet=options" method="post">
                                        <br />
                                        <input type="checkbox" name="partage"
                                        <?php
                                        if ($resultat_partage[0]['AccepterPartage']==1) {?>
                                            checked="checked"
                                        <?php } ?>
                                         /><?=ACTIVER_PARTAGE?><br />
                                        <input type="checkbox" name="video"
                                        <?php
                                        if (DM_Core::$d->user_afficher_video()) {?>
                                            checked="checked"
                                        <?php } ?>
                                         /><?=AFFICHER_VIDEO?><br />
                                        <br />
                                        <input name="submit_options" class="valider" type="submit" value="<?=VALIDER?>" /></form>
                                        <br /><br /><br />
                                        <?php
                                        if (isset($_GET['vider']) || isset($_GET['supprimer'])) {
                                            if (isset($_GET['confirm']) && $_GET['confirm']=='true') {
                                                $action=isset($_GET['vider'])?'vider':'supprimer';
                                                switch ($action) {
                                                    case 'vider':
                                                        $requete='DELETE FROM numeros WHERE ID_Utilisateur='.$id_user;
                                                        DM_Core::$d->requete($requete);
                                                        echo NUMEROS_SUPPRIMES.'.<br />';
                                                        break;
                                                    case 'supprimer':
                                                        $requete='DELETE FROM numeros WHERE ID_Utilisateur='.$id_user;
                                                        DM_Core::$d->requete($requete);
                                                        echo NUMEROS_SUPPRIMES.'<br />';
                                                        $requete_compte='DELETE FROM users WHERE ID='.$id_user;
                                                        DM_Core::$d->requete($requete_compte);
                                                        session_destroy();
                                                        echo COMPTE_SUPPRIME_DECONNECTE.'<br />';
                                                        break;
                                                }
                                            }
                                            else {
                                                ?>
                                                <?=OPERATION_IRREVERSIBLE?><br /><?=CONTINUER_OUI_NON?><br />
                                                <a href="?action=gerer&amp;onglet=compte&amp;<?php isset($_GET['vider'])?'vider':'supprimer'?>=true&amp;confirm=true">
                                                    <button><?=OUI?></button></a>&nbsp;
                                                <a href="?action=gerer">
                                                    <button><?=NON?></button></a>
                                                <?php
                                            }
                                        }
                                        else {
                                            ?>
                                            <a href="?action=gerer&amp;onglet=compte&amp;vider=true"><?=VIDER_LISTE?></a><br /><br />
                                            <a href="?action=gerer&amp;onglet=compte&amp;supprimer=true"><?=SUPPRIMER_COMPTE?></a><br />
                                            <?php
                                        }

                                        break;
                                    case 'ajout_suppr':
                                        /*if (isset($_POST['supprimer_doublons'])) {
                                            
                                        }
                                        $id_user=DM_Core::$d->user_to_id($_SESSION['user']);

                                        $requete_doublons='SELECT Pays,Magazine,Numero FROM numeros '
                                                         .'GROUP BY Pays, Magazine, Numero, Id_Utilisateur '
                                                         .'HAVING COUNT(*) > 1 AND  Id_Utilisateur ='.$id_user.' '
                                                         .'ORDER BY Id_Utilisateur, Pays, Magazine, Numero';
                                        echo $requete_doublons;$resultat_doublons=DM_Core::$d->requete_select($requete_doublons);
                                        if (count($resultat_doublons)>0) {
                                            ?><h3><?=AVERTISSEMENT?></h3><?php
                                            echo AVERTISSEMENT_DOUBLONS_1.' '.count($resultat_doublons).' '.AVERTISSEMENT_DOUBLONS_2;
                                            $liste_doublons=new Liste();
                                            foreach($resultat_doublons as $doublon) {
                                                $liste_doublons->ajouter($doublon['Pays'], $doublon['Magazine'], $doublon['Numero']);
                                            }
                                            $liste_doublons->afficher('Classique');
                                            echo AVERTISSEMENT_DOUBLONS_3;
                                            ?><form action="">
                                                <input type="hidden" name="action" value="gerer" />
                                                <input type="hidden" name="supprimer_doublons" value="true" />
                                                <input type="submit" value="<?=SUPPRIMER_DOUBLONS?>" />
                                            </form><br /><br /><hr /><?php
                                        }*/
                                        ?>
                                        <?=POSSESSION_MAGAZINES_1?><br /><?=POSSESSION_MAGAZINES_2?><br />
                                        <?php

                                        $onglets_magazines=$l->liste_magazines();

                                        $onglets_pays=$l->liste_pays();
                                        if (isset($_POST['magazine'])) {
                                            $onglets_pays[$_POST['pays']]=array($_POST['pays'],NOUVEAU_PAYS);
                                            $onglets_magazines[$_POST['pays'].'/'.$_POST['magazine']]=array($_POST['pays'].'/'.$_POST['magazine'],NOUVEAU_MAGAZINE);
                                        }
                                        else {
                                            $onglets_pays[NOUVEAU_MAGAZINE]=array('new',AJOUTER_MAGAZINE);
                                        }
                                        if (!isset($_GET['onglet_magazine'])) {
                                            $onglet_pays=null;
                                            $onglet_magazine=null;
                                        }
                                        else {
                                            if (isset($_POST['magazine'])) {
                                                $onglet_pays=$_POST['pays'];
                                                $onglet_magazine=$_POST['pays'].'/'.$_POST['magazine'];
                                            }
                                            else {
                                                $onglet_pays=substr($_GET['onglet_magazine'],0,  strpos($_GET['onglet_magazine'], '/'));
                                                $onglet_magazine=$_GET['onglet_magazine'];
                                            }
                                        }
                                        Affichage::onglets($onglet_pays,$onglets_pays,'','',true);
                                        Affichage::onglets($onglet_magazine,$onglets_magazines,'onglet_magazine','?action=gerer&amp;onglet=ajout_suppr');
                                        ?><span id="nom_magazine_courant" style="visibility:hidden;border:1px solid white;display:table;color:#666666;margin-top:-15px;background-color:#C88964">&nbsp;
                                        </span><br /><?php

                                        if ($onglet_magazine=='new' && !isset($_POST['magazine'])) {
                                            echo REMPLIR_INFOS_NOUVEAU_MAGAZINE;
                                            ?>
                        <br /><br />
                        <form method="post" action="?action=gerer&amp;onglet=ajout_suppr&amp;onglet_magazine=new">
                            <input type="hidden" id="form_pays" name="pays" value="" />
                            <input type="hidden" id="form_magazine" name="magazine" value="" />
                            <input type="hidden" name="onglet_magazine" value="new" />
                            <span style="text-decoration:underline"><?=PAYS_PUBLICATION?> : </span><br />
                            <select style="width:300px;" onchange="select_magazine()" id="liste_pays">
                                <option id="chargement_pays"><?=CHARGEMENT?>...
                            </select><br /><br />
                            <span style="text-decoration:underline"><?=MAGAZINE?> : </span><br />
                            <select style="width:300px;" onchange="magazine_selected()" id="liste_magazines">
                                <option id="vide"><?=SELECTIONNER_PAYS?>
                            </select>
                            <br /><br />
                            <input type="submit" class="valider" value="<?=VALIDER?>" />
                        </form><br /><br />
                                            <?php
                                        }
                                        else {
                                            if (isset($onglet_magazine) && isset($pays)) {
                                            ?>
                                                <?php if (isset($_GET['afficher_video']) && $_GET['afficher_video']==0) {
                                                    $requete_cacher_video='UPDATE users SET AfficherVideo=0 WHERE ID='.$id_user;
                                                    DM_Core::$d->requete($requete_cacher_video);
                                                }
                                                if (DM_Core::$d->user_afficher_video()) { ?>
                                                    <br /><br />
                                                    <div style="width:742px"><div style="float: right;"><a href="<?=$_SERVER['REQUEST_URI']?>&amp;afficher_video=0"><?=CACHER_VIDEO?></a></div></div>
                                                    <OBJECT CLASSID="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" WIDTH="742" HEIGHT="397" CODEBASE="http://active.macromedia.com/flash5/cabs/swflash.cab#version=7,0,0,0">
                                                        <PARAM NAME=movie VALUE="dm.swf">
                                                        <PARAM NAME=play VALUE=false>
                                                        <PARAM NAME=loop VALUE=false>
                                                        <PARAM NAME=wmode VALUE=transparent>
                                                        <PARAM NAME=quality VALUE=low>
                                                        <EMBED SRC="dm.swf" WIDTH=742 HEIGHT=397 play=false quality=low loop=false wmode=transparent TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" />
                                                    </OBJECT>
                                                <?php } ?>
                                                <table width="100%">
                                                <tr><td>
                                                <span id="liste_numeros"><?=CHARGEMENT.'...'?></span>
                                                </td><td>
                                            </td></tr></table>
                                            <?php
                                            }
                                        }
                                        break;
                                }

                                break;
                            case 'stats':
                                ?>
                                <h2><?=STATISTIQUES_COLLECTION?></h2><br />
                                <?php
                                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                $l=DM_Core::$d->toList($id_user);
                                if (!isset($_GET['onglet']))
                                    $onglet='magazines';
                                else
                                    $onglet=$_GET['onglet'];
                                $l->statistiques($onglet);
                                break;

                            case 'print':
                                if ($_SESSION['user']!='nonoox') {
                                    ?><img width="300" src="images/travaux.png" /><br />
                                    <?=TRAVAUX_SECTION?>
                                    <?php break;
                                }
                                ?>
                                <span style="font-weight: bold; text-decoration: underline;"><?=IMPRESSION_COLLECTION?> : </span><br /><br />
                                <?=INTRO_IMPRESSION_COLLECTION1?><br />
                                <?=INTRO_IMPRESSION_COLLECTION2?><br />
                                <br />
                                <div style="text-align:center;border:1px solid white;">
                                    <a target="_blank" href="print.php"><?=CLIC_IMPRESSION?>!</a>
                                </div><br /><br />
                                <?=INTRO_IMPRESSION_COLLECTION3?><br /><br />
                                <table border="1" cellpadding="4" cellspacing="2">
                                    <tr align="center">
                                        <td><?=AFFICHAGE_LISTE?></td>
                                        <td><?=DESCRIPTION?></td>
                                    </tr>
                                <?php
                                $liste_exemple=new Liste();
                                $liste_exemple->ListeExemple();

                                $rep = "Listes/";
                                global $types_listes;
                                $types_listes=array();
                                $dir = opendir($rep);
                                $prefixe='Liste.';
                                $suffixe='.class.php';
                                while ($f = @readdir($dir)) {
                                    if (strpos($f,'Debug')!==false)
                                        continue;
                                    if(is_file($rep.$f)) {
                                        if (startsWith($f,$prefixe) && endsWith($f,$suffixe)) {
                                            array_push($types_listes,substr($f,strlen($prefixe),strlen($f)-strlen($suffixe)-strlen($prefixe)));
                                            require_once($rep.$f);
                                        }
                                    }
                                }
                                
                                foreach($types_listes as $type) {
                                    if ($type=='Series') continue;
                                    $objet =new $type();
                                    ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if ($type=='DMtable') {
                                                ?>
                                                <iframe height="200px" width="100%" src="Liste.class.php?liste_exemple=true&amp;type_liste=<?=$type?>"></iframe>
                                                <?php
                                            }
                                            else
                                                echo $objet->afficher($liste_exemple->collection);
                                            ?>
                                        </td>
                                        <td>
                                            <?=$objet->description?><br /><br />
                                            <img src="plus.png" alt="plus" /><br />
                                            <?php
                                            foreach($objet->les_plus as $plus) {
                                                ?>- <?=$plus?><br /><?php
                                            }
                                            ?>
                                            <br />
                                            <img src="moins.png" alt="moins"/><br />
                                            <?php
                                            foreach($objet->les_moins as $moins) {
                                                ?>- <?=$moins?><br /><?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </table>
                                <?php
                                break;

                            case 'agrandir':
                                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                $l=DM_Core::$d->toList($id_user);

                                $onglets=array(ACHAT_VENTE_NUMEROS=>array('achat_vente',CONTACT_UTILISATEURS),
                                               AUTEURS_FAVORIS=>array('auteurs_favoris',AUTEURS_FAVORIS_TEXTE),
                                               //COMPLETER_SERIES=>array('completer_series',COMPLETER_SERIES_TEXTE),
                                               RECHERCHER_BOUQUINERIES=>array('bouquineries',RECHERCHER_BOUQUINERIES_TEXTE));
                                if (!isset($_GET['onglet']))
                                    $onglet='achat_vente';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet,$onglets,'onglet','?action=agrandir');
                                switch($onglet) {
                                    case 'achat_vente':
                                        ?>
                                        <?=INTRO_ACHAT_VENTE?><br />
                                        <?php
                                        $accepte=DM_Core::$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        if ($accepte[0]['AccepterPartage']==0) {
                                            echo COMMENT_PARTAGER_COLLECTION;
                                            ?>
                                            <i><a href="?action=gerer&amp;onglet=options"><?=PAGE_OPTIONS?></a></i>
                                            <?php
                                        }
                                        DM_Core::$d->liste_numeros_externes_dispos($id_user);
                                        break;
                                    case 'auteurs_favoris':
                                        $onglets_auteurs=array(RESULTATS_SUGGESTIONS_MAGS=>array('resultats',SUGGESTIONS_ACHATS),
                                                               PREFERENCES_AUTEURS=>array('preferences',PREFERENCES_AUTEURS));
                                        if (!isset($_GET['onglet_auteur']))
                                            $onglet_auteurs='resultats';
                                        else
                                            $onglet_auteurs=$_GET['onglet_auteur'];
                                        Affichage::onglets($onglet_auteurs,$onglets_auteurs,'onglet_auteur','?action=agrandir&amp;onglet=auteurs_favoris');
                                        echo PRESENTATION_AUTEURS_FAVORIS;
                                        switch ($onglet_auteurs) {
                                            case 'resultats':
                                                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                                $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
                                                $resultat_auteurs_surveilles=DM_Core::$d->requete_select($requete_auteurs_surveilles);
                                                ?>
                                                <br /><br />
                                                <?=SUGGESTIONS_ACHATS_QUOTIDIENNES?><br />
                                                <?php
                                                $auteur_note_existe=false;
                                                foreach($resultat_auteurs_surveilles as $auteur_surveille) {
                                                    if ($auteur_surveille['Notation']!=-1) $auteur_note_existe=true;
                                                }
                                                if (count($resultat_auteurs_surveilles)>0) {
                                                    if (!$auteur_note_existe) echo AUTEURS_NON_NOTES;
                                                    else {
                                                        echo LANCER_CALCUL_SUGGESTIONS_MANUELLEMENT;
                                                        ?>
                                                        <br />
                                                        <button onclick="stats_auteur(<?=$id_user?>)"><?=LANCER_CALCUL_SUGGESTIONS?></button>
                                                        <div id="resultat_stats"></div>
                                                        <?php
                                                    }
                                                }
                                                else echo AUCUN_AUTEUR_SURVEILLE;
                                                ?>
                                                <br /><br />
                                                <?php
                                                DM_Core::$d->liste_suggestions_magazines();
                                                break;
                                            case 'preferences':
                                                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                                if (isset($_POST['auteur_nom'])) {
                                                    DM_Core::$d->ajouter_auteur($_POST['auteur_id'],$_POST['auteur_nom']);
                                                }
                                                ?>
                                                <br /><br />
                                                <?=AUTEURS_FAVORIS_INTRO_1?>
                                                <br />
                                                <?=STATISTIQUES_AUTEURS_INTRO_2?>
                        <br /><br />
                        <form method="post" action="?action=agrandir&amp;onglet=auteurs_favoris&amp;onglet_auteur=preferences">
                            <input type="text" name="auteur_cherche" id="auteur_cherche" value="" size="40"/>
                            <div class="update" id="liste_auteurs"></div>
                            <input type="hidden" id="auteur_nom" name="auteur_nom" />
                            <input type="hidden" id="auteur_id" name="auteur_id" />
                            <img alt="Loading" id="loading_auteurs" src="loading.gif" style="display:none" />
                            <input type="submit" value="Ajouter" />
                        </form>
                        <div id="auteurs_ajoutes">
                            <br /><br />
                                                    <?php
                                                    echo LISTE_AUTEURS_INTRO;
                                                    if (isset($_POST['auteur0'])) {
                                                        $recommandations_liste_mags=($_POST['proposer_magazines_possedes']==='on'?1:0);
                                                        $requete_update_recommandations_liste_mags='UPDATE users SET RecommandationsListeMags='.$recommandations_liste_mags.' '
                                                                .'WHERE ID='.$id_user;
                                                        DM_Core::$d->requete($requete_update_recommandations_liste_mags);
                                                    }
                                                    $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
                                                    $resultat_auteurs_surveilles=DM_Core::$d->requete_select($requete_auteurs_surveilles);
                                                    foreach($resultat_auteurs_surveilles as $auteur) {
                                                        $i=0;
                                                        while ($_POST['auteur'.$i]) {
                                                            if ($_POST['auteur'.$i] == $auteur['NomAuteurAbrege']) {
                                                                $aucune_note=($_POST['aucune_note'.$i]==='on'?1:0);
                                                                if (!empty($_POST['notation'.$i]) || $aucune_note) {
                                                                    $notation=$aucune_note?-1:$_POST['notation'.$i];
                                                                    $requete_notation='UPDATE auteurs_pseudos SET Notation='.$notation.' '
                                                                            .'WHERE DateStat LIKE \'0000-00-00\' AND NomAuteurAbrege LIKE \''.$_POST['auteur'.$i].'\' '
                                                                            .'AND ID_user='.$id_user;
                                                                    DM_Core::$d->requete($requete_notation);
                                                                }
                                                            }
                                                            $i++;
                                                        }
                                                    }
                                                    $resultat_auteurs_surveilles=DM_Core::$d->requete_select($requete_auteurs_surveilles);
                                                    DM_Core::$d->liste_auteurs_surveilles($resultat_auteurs_surveilles,true);
                                                    ?>
                                                </div><?php
                                                break;
                                        }
                                        break;
                                    case 'completer_series':
                                        echo INTRO_COMPLETER_SERIES.'<br /><br />';
                                        break;
                                    case 'bouquineries':
                                        echo INTRO_BOUQUINERIES.'<br />';
                                        if (isset($_POST['ajouter'])) {
                                            $requete='INSERT INTO bouquineries(Nom, Adresse, CodePostal, Ville, Pays, Commentaire, ID_Utilisateur) VALUES (\''.$_POST['nom'].'\',\''.$_POST['adresse'].'\',\''.$_POST['cp'].'\',\''.$_POST['ville'].'\',\'France\',\''.$_POST['commentaire'].'\','.$id_user.')';
                                            ?>
                                            <span style="color:red">
                                            <?php
                                            if ($id_user==1)
                                                DM_Core::$d->requete($requete);
                                            else {
                                                mail('admin@ducksmanager.net','Ajout de bouquinerie',$requete);
                                                echo EMAIL_ENVOYE.EMAIL_ENVOYE_BOUQUINERIE;
                                            }
                                            echo MERCI_CONTRIBUTION;
                                            ?>
                                            </span><br />
                                            <?php
                                        }
                                        ?>
                                        <h2><?=LISTE_BOUQUINERIES?></h2>
                                        <iframe src="bouquineries.php" width="70%" height="700px"></iframe>
                                        <br /><br />
                                        <?php
                                        $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
                                        ?>
                                        <h2><?=PROPOSER_BOUQUINERIE?></h2>
                                        <?=PRESENTATION_BOUQUINERIE1?><br />
                                        <?=INTRO_NOUVELLE_BOUQUINERIE?><br />
                                        <?=PRIX_HONNETES?>
                                        <br /><br />
                                        <form method="post" action="?action=agrandir&amp;onglet=bouquineries">
                                            <table border="0">
                                                <tr><td><?=NOM_BOUQUINERIE?> :</td><td><input maxlength="25" size="26" name="nom" type="text" /></td></tr>
                                                <tr><td><?=ADRESSE?> :</td><td><textarea cols="20" name="adresse"></textarea></td></tr>
                                                <tr><td><?=CODE_POSTAL?> :</td><td><input maxlength="11" name="cp" type="text" size="5" maxlength="5"/></td></tr>
                                                <tr><td><?=VILLE?> :</td><td><input maxlength="20" size="26" name="ville" type="text" /></td></tr>
                                                <tr><td><?=COMMENTAIRES_BOUQUINERIE?><br />(<?=COMMENTAIRES_BOUQUINERIE_EXEMPLE?>)</td>
                                                    <td><textarea name="commentaire" colspan="40" rowspan="5"></textarea></td></tr>
                                        <?php
                                        //echo '<tr><td>Pays :</td><td><input name="pays" type="text" /></td></tr>';
                                        /*echo '<tr><td colspan="2"><div style="border:1px solid white;"><u>Exemples de prix : </u><br />';
                                        echo '<div id="liste_exemples"></div>';
                                        echo '<span id="ajouter_exemple"></span></div>';
                                        echo '<a href="javascript:void(0)" onclick="ajouter_exemple()">Ajouter un exemple de prix</a></td></tr>';*/
                                        ?>
                                                <tr><td align="center" colspan="2"><input name="ajouter" type="submit" value="<?=AJOUTER_BOUQUINERIE?>" /></td></tr>
                                            </table>
                                        </form>
                                        <?php
                                        break;
                                }

                                break;

                            default:
                                ?>
                                <br /><br />
                                <?php/*
                                <?=PRESENTATION1?><br /><br />
                                <?=PRESENTATION2?><br /><br /><br /><?php */?>
                                <table>
                                    <tr>
                                        <td style="width:400px;border:1px solid gray;padding:10px">
                                            <img alt="demo 2_1" src="demo2_1.png" />
                                        </td>
                                        <td style="background-color:gray;vertical-align:top;padding-left:10px;width:650px;">
                                            <h3><?=PRESENTATION_GERER_TITRE?></h3>
                                            <br /><br />
                                            <?=PRESENTATION_GERER_1?>
                                            <br /><br />
                                            <?=PRESENTATION_GERER_2?>
                                            <br /><br />
                                            <?=PRESENTATION_GERER_3?><div style="height:30px"></div>
                                            <div style="border:1px solid gray;padding:10px">
                                                <img src="demo2_2.png" alt="demo2_2"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="height:50px"></tr>
                                    <tr>
                                        <td style="background-color:gray;vertical-align:top;width:350px">
                                            <h3><?=PRESENTATION_STATS_TITRE?></h3>
                                            <br /><br />
                                            <?=PRESENTATION_STATS_1?>
                                            <br /><br />
                                            <?=PRESENTATION_STATS_2?>
                                            <br /><br />
                                            <?=PRESENTATION_STATS_3?>
                                            <br /><br />
                                            <?=ANNONCE_AGRANDIR_COLLECTION1?>
                                            <br />
                                            <div style="height:35px"></div>
                                            <div style="border:1px solid gray;padding:10px">
                                                <img src="images/demo3.png" alt="demo3"/>
                                            </div>
                                        </td>
                                        <td style="vertical-align:top;width:600px;border:1px solid gray;padding:10px">
                                            <img width="300" alt="demo 1" src="images/demo1.png" />&nbsp;
                                            <img alt="demo 2" src="images/demo2.png" />
                                        </td>
                                    </tr>
                                    <tr style="height:50px"></tr>
                                    <tr>
                                        <td style="vertical-align:top;width:550px;border:1px solid gray;padding:10px">
                                            <img alt="demo b" width="550" src="demo_bibliotheque.png" />
                                        </td>
                                        <td style="background-color:gray;vertical-align:top;padding-left:10px;width:650px;">
                                            <h3><?=PRESENTATION_BIBLIOTHEQUE_TITRE?></h3>
                                            <br />  
                                            <?=PRESENTATION_BIBLIOTHEQUE_1?>
                                            <br /><br />
                                            <?=PRESENTATION_BIBLIOTHEQUE_2?>
                                            <br /><br />
                                            <?=PRESENTATION_BIBLIOTHEQUE_3?><div style="height:30px"></div>
                                            <div style="border:1px solid gray;padding:10px">
                                                <img src="demo_bibliotheque2.png" alt="demo2_2"/>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <br />
                                
                                <div style="border:1px solid white;text-align:center;">
                                    <?=PRESENTATION_GENERALE?>.<br />
                                    <h3><?=BIENVENUE?></h3>
                                </div>
                                <?=GRATUIT_AUCUNE_LIMITE?> <a href="?action=new"><?=INSCRIVEZ_VOUS?></a>
                                <?php
                                break;
                        }
                        fin_de_page();

                        function fin_de_page() {
                            ?>
                    </div>
                </td>

            </tr>
            <tr style="height:3px;background-color:black;"><td colspan="5"></td></tr>
            <tr style="height:20px">
                <td align="center" style="padding-left:4px;width: 242px;">
                        <?php
                        $resultat_cpt_users=DM_Core::$d->requete_select('SELECT count(username) as cpt_users FROM users');
                        echo $resultat_cpt_users[0]['cpt_users'].' '.UTILISATEURS_INSCRITS;
                        ?>
                </td>
                <td colspan="2" align="center">
                        <?=LICENCE_INDUCKS1?>
                        <a target="_blank" href="http://coa.inducks.org/inducks/COPYING"><?=LICENCE_INDUCKS2?></a>
                        <br />
                        <?=LICENCE_INDUCKS3?>
                </td>
                <td valign="bottom" align="right">
                        <?php
                        $rep = "locales/";
                        $dir = opendir($rep);
                        while ($f = readdir($dir)) {
                            if(is_file($rep.$f)) {
                                if (endsWith($f,'.php') && strpos($f,'lang')===false) {
                                    $nom_langue=substr($f,0,strrpos($f,'.'));
                                    ?>
                                    <a href="?<?=str_replace('&','&amp;',$_SERVER['QUERY_STRING'])?>&amp;lang=<?=$nom_langue?>">
                                          <img style="border:0" src="images/<?=$nom_langue?>.jpg" alt="<?=$nom_langue?>"/>
                                    </a>
                                    <?php
                                }
                            }
                        }
                        ?>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
    <?php
}

function formulaire_inscription() {
    if (isset($_POST['user'])) {
        if (strlen($_POST['pass']) <6) {
            $erreur=MOT_DE_PASSE_6_CHAR_ERREUR;
        }
        elseif ($_POST['pass'] != $_POST['pass2']) {
            $erreur=MOTS_DE_PASSE_DIFFERENTS;
        }
        else {
            if (DM_Core::$d->user_exists($_POST['user']))
                $erreur=UTILISATEUR_EXISTANT;
        }
        if ($erreur) {
            ?><span style="color:red"><?=$erreur?></span><?php
        }
    }
    if (!isset($_POST['user']) || isset($erreur)) {
        ?>
        <form method="post" action="index.php?action=new">
        <?php
        if (isset($_POST['rawData'])) {
            ?><input type="hidden" name="rawData" value="<?=$_POST['rawData']?>" /><?php
        }?>
        <table border="0"><tr><td><?=NOM_UTILISATEUR?> : </td><td><input name="user" type="text">&nbsp;</td></tr>
            <tr><td><?=MOT_DE_PASSE_6_CHAR?> :</td><td><input name="pass" type="password" /></td></tr>
            <tr><td><?=MOT_DE_PASSE_CONF?> :</td><td><input name="pass2" type="password" /></td></tr>
            <tr><td colspan="2"><input type="submit" value="<?=INSCRIPTION?>" /></td></tr></table>
        </form>
        <?php
    }
    else {
        DM_Core::$d->nouveau_user($_POST['user'], $_POST['pass']);
        if (isset($_POST['rawData'])) {
            $l = new Liste($_POST['rawData']);
            $l->add_to_database(DM_Core::$d, DM_Core::$d->user_to_id($_POST['user']));
        }
        creer_id_session($_POST['user'], $_POST['pass']);
    }
}

function creer_id_session($user,$pass) {
    setCookie('user',$user,time()+3600);
    setCookie('pass',sha1($pass),time()+3600);
    $_SESSION['user']=$user;
    header('Location: index.php?action=gerer');
}
?>