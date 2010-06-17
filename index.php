<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(  E_ALL & ~E_NOTICE & ~E_DEPRECATED );
@session_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
error_reporting(E_ERROR);
ini_set('session.lifetime', 0);
require_once('Liste.class.php');
require_once('JS.class.php');
require_once('Menu.class.php');
require_once('Affichage.class.php');
require_once('Inducks.class.php');
require_once('Util.class.php');

$menu=	array(COLLECTION=>
        array("new"=>
                array("private"=>"never",
                        "link"=>true,
                        "text"=>NOUVELLE_COLLECTION),
                "open"=>
                array("private"=>"never",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>OUVRIR_COLLECTION),
                "bibliotheque"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>BIBLIOTHEQUE_COURT),
                "gerer"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>GERER_COLLECTION),
                "stats"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>STATISTIQUES_COLLECTION),
                "agrandir"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>AGRANDIR_COLLECTION),
                "print"=>
                array("private"=>"always",
                        "link"=>true,
                        "text"=>IMPRIMER_COLLECTION),
                "logout"=>
                array("private"=>"always",
                        "link"=>true,
                        "text"=>DECONNEXION))
        ,
        COLLECTION_INDUCKS=>
        array("import"=>
                array("private"=>"no",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>IMPORTER_INDUCKS)/*,
				      "export"=>
					array("private"=>"no",
						  "link"=>false,
						  "text"=>EXPORTER_INDUCKS*/
        )
        )
;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
    <head>
        <meta content="text/html; charset=ISO-8859-1"
              http-equiv="content-type">
        <title><?php echo TITRE;?></title>
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
        new JS('prototype.js');
        new JS('js/scriptaculous/src/scriptaculous.js');
        new JS('js/my_scriptaculous.js');
        new JS('js/ajax.js');
        if (isset($_GET['action'])) {
            new JS('js/sel_num.js');
            if ($_GET['action']=='gerer')
                new JS('js/menu_contextuel.js');
            new JS('js/selection_menu.js');
            new JS('js/bouquineries.js');
            new JS('js/divers.js');
            if ($_GET['action']=='bibliotheque')
                new JS('js/edges.js');
        }
        ?>
    </head>

    <?php
    $action=isset($_GET['action'])?$_GET['action']:null;
    $texte_debut='';
    $d=new Database();
    if ($action=='open'&& isset($_POST['user'])) {
        if (!$d->user_connects($_POST['user'],$_POST['pass']))
            $texte_debut.= 'Identifiants invalides!<br /><br />';
        else {
            setCookie('user',$_POST['user'],time()+3600);
            setCookie('pass',sha1($_POST['pass']),time()+3600);
            $_SESSION['user']=$_POST['user'];
            header('Location: index.php?action=gerer');
        }
    }
    else {
        if (isset($_COOKIE['user']) && isset($_COOKIE['pass'])) {
            if (!$d->user_connects($_COOKIE['user'],$_COOKIE['pass'])) {
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
        case 'import':
            if (isset($_POST['user'])) {
                echo 'appel('.appel().');';
            }
            else echo 'defiler_log(\'DucksManager\');';
            break;
        case 'open':
            echo 'defiler_log(\'DucksManager\');';
            break;
        case 'bibliotheque':
            if ((!isset($_GET['onglet']) || $_GET['onglet']=='affichage') && Util::getBrowser()!=='MSIE') {
                $d=new Database();
                if (!$d) {
                    exit(-1);
                }
                $id_user=$d->user_to_id($_SESSION['user']);
                $textures=array();
                for ($i=1;$i<=2;$i++) {
                    $requete_texture='SELECT Bibliotheque_Texture'.$i.' FROM users WHERE ID LIKE \''.$id_user.'\'';
                    $resultat_texture=$d->requete_select($requete_texture);
                    $textures[]=$resultat_texture[0]['Bibliotheque_Texture'.$i];
                    $requete_sous_texture='SELECT Bibliotheque_Sous_Texture'.$i.' FROM users WHERE ID LIKE \''.$id_user.'\'';
                    $resultat_sous_texture=$d->requete_select($requete_sous_texture);
                    $textures[]=$resultat_sous_texture[0]['Bibliotheque_Sous_Texture'.$i];
                }
                echo 'charger_bibliotheque(\''.$textures[0].'\',\''.$textures[1].'\', \''.$textures[2].'\',\''.$textures[3].'\');';
            }
            elseif ($_GET['onglet']=='options') {
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
                                    foreach($menu as $item=>$infos) {
                                        ?>
                                        <span style="font-weight: bold; text-decoration: underline;"><?=$item?></span><br />
                                        <?php
                                        foreach($infos as $sous_item=>$infos_sous_item) {
                                            if ($infos_sous_item['private']=='no') {
                                                if (!$infos_sous_item['link']) {
                                                    ?>
                                                    <del><?=$infos_sous_item['text']?></del><br />';
                                                    <?php
                                                    continue;
                                                }
                                                ?>
                                                <a href="?action=<?=$sous_item?>"><?=$infos_sous_item['text']?></a><br>
                                                <?php
                                            }
                                            else {
                                                if (isset($_SESSION['user']) &&!($action=='logout')) {
                                                    if (!$infos_sous_item['link']) {
                                                        ?>
                                                        <del><?=$infos_sous_item['text']?></del><br />
                                                        <?php
                                                        continue;
                                                    }
                                                    if ($infos_sous_item['private']=='always'){
                                                        ?>
                                                        <a href="?action=<?=$sous_item?>"><?=$infos_sous_item['text']?></a><br>
                                                        <?php
                                                    }
                                                }
                                                else if ($infos_sous_item['private']=='never'){
                                                    if (!$infos_sous_item['link']) {
                                                        ?>
                                                        <del><?=$infos_sous_item['text']?></del><br />
                                                        <?php
                                                        continue;
                                                    }
                                                    ?>
                                                    <a href="?action=<?=$sous_item?>"><?=$infos_sous_item['text']?></a><br>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                        <br />
                                        <?php
                                    }
                                    /*if (isset($_SESSION['user']) &&!($action=='logout')){
				echo '&nbsp;<a href="?action=gerer">G&eacute;rer ma collection</a><br>'
					.'&nbsp;Sauvegarder ma collection<br>'
					.'&nbsp;Imprimer ma collection<br>'
					.'&nbsp;<a class="important" href="?action=logout">D&eacute;connexion</a><br>';
			}
			else {
				echo '&nbsp;Nouvelle collection<br>'
					.'&nbsp;<a href="?action=open">Ouvrir ma collection</a><br>';
			}*/
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
                        foreach($menu as $item=>$infos) {
                            foreach($infos as $sous_item=>$infos_sous_item) {
                                if ($sous_item==$action) {
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
                                    if ($infos_sous_item['private']=='always' && !isset($_SESSION['user'])) {
                                        echo IDENTIFICATION_OBLIGATOIRE.'<br />';
                                        echo COMMENT_S_IDENTIFIER;
                                        $action='aucune';
                                    }
                                }
                            }
                        }
                        switch($action) {
                            case 'import':
                            /*if (isset($_SESSION['user'])) {
			echo IMPORT_IMPOSSIBLE_SI_CONNECTE1.'<br />';
			echo IMPORT_IMPOSSIBLE_SI_CONNECTE2;
			break;
		}*/
                                if (!isset($_POST['user'])) {
                                    afficher_form_inducks();
                                }
                                else echo IMPORTATION_EN_COURS;
                                break;
                            case 'new':
                                ?>
                                <table><tr><td colspan="2"></td></tr>
                                    <tr><td><span id="user_text"><?=NOM_UTILISATEUR?> : </span></td><td><input id="user" type="text">&nbsp;</td></tr>
                                    <tr><td><span id="pass_text"><?=MOT_DE_PASSE_6_CHAR?> : </span></td><td><input id="pass" type="password">&nbsp;</td></tr>
                                    <tr><td><span id="pass_text2"><?=MOT_DE_PASSE_CONF?> : </span></td><td><input id="pass2" type="password">&nbsp;</td></tr>
                                    <tr><td colspan="2"><input type="submit" value="<?=INSCRIPTION?>" onclick="verif_valider_inscription($('user'),$('pass'),$('pass2'),false)"></td></tr></table>
                                <?php
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
                                        BIBLIOTHEQUE_PARTICIPER_COURT=>array('participer',BIBLIOTHEQUE_PARTICIPER));
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
                                            <br /><br />
                                            <div id="bibliotheque" style="width:100%;height:100%"></div>
                                            <?php
                                        }
                                    break;
                                    case 'options':
                                        if (isset($_POST['texture1'])) {
                                            $d=new Database();
                                            if (!$d) {
                                                echo PROBLEME_BD;
                                                exit(-1);
                                            }
                                            $id_user=$d->user_to_id($_SESSION['user']);
                                            for ($i=1;$i<=2;$i++) {
                                                $requete_update_texture='UPDATE users SET Bibliotheque_Texture'.$i.'=\''.$_POST['texture'.$i].'\'';
                                                echo $requete_update_texture;
                                                $d->requete($requete_update_texture);
                                                $requete_update_sous_texture='UPDATE users SET Bibliotheque_Sous_Texture'.$i.'=\''.$_POST['sous_texture'.$i].'\'';
                                                $d->requete($requete_update_sous_texture);
                                            }
                                        }
                                        ?><form method="post" action="?action=bibliotheque&amp;onglet=options">
                                            <span style="text-decoration:underline"><?=TEXTURE?> : </span><br />
                                            <select style="width:300px;" id="texture1" name="texture1">
                                                <option id="chargement_sous_texture"><?=CHARGEMENT?>...</option>
                                            </select>
                                            <br /><br />
                                            <span style="text-decoration:underline"><?=SOUS_TEXTURE?> : </span><br />
                                            <select style="width:300px;" id="sous_texture1" name="sous_texture1">
                                                <option id="vide"><?=SELECTIONNER_TEXTURE?>
                                            </select>
                                            <br /><br /><br />
                                            <span style="text-decoration:underline"><?=TEXTURE_ETAGERE?> : </span><br />
                                            <select style="width:300px;" id="texture2" name="texture2">
                                                <option id="chargement_sous_texture"><?=CHARGEMENT?>...</option>
                                            </select>
                                            <br /><br />
                                            <span style="text-decoration:underline"><?=SOUS_TEXTURE_ETAGERE?> : </span><br />
                                            <select style="width:300px;" id="sous_texture2" name="sous_texture2">
                                                <option id="vide"><?=SELECTIONNER_TEXTURE?>
                                            </select>
                                            <br /><br />
                                            <input type="submit" class="valider" value="<?=VALIDER?>" />
                                        </form>
                                        <?php

                                    break;
                                }
                            break;

                            case 'gerer':
                                $d=new Database();
                                if (!$d) {
                                    echo PROBLEME_BD;
                                    exit(-1);
                                }
                                $id_user=$d->user_to_id($_SESSION['user']);
                                $l=$d->toList($id_user);
                                ?>
                                <h2><?=GESTION_COLLECTION?></h2><br />
                                <?php
                                $onglets=array(
                                        GESTION_NUMEROS_COURT=>array('ajout_suppr',GESTION_NUMEROS),
                                        GESTION_ACQUISITIONS_COURT=>array('acquisitions',GESTION_ACQUISITIONS),
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
                                            if ($_POST['partage']=='on')
                                                $d->requete('UPDATE users SET AccepterPartage=1 WHERE ID='.$id_user);
                                            else
                                                $d->requete('UPDATE users SET AccepterPartage=0 WHERE ID='.$id_user);
                                        }
                                        $resultat_partage=$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        ?>
                                        <form action="?action=gerer&amp;onglet=options" method="post">
                                        <br /><input type="checkbox" name="partage"
                                        <?php
                                        if ($resultat_partage[0]['AccepterPartage']==1) {?>
                                            checked="checked"
                                        <?php } ?>
                                         /><?=ACTIVER_PARTAGE?><br />
                                        <input name="submit_options" class="valider" type="submit" value="<?=VALIDER?>" /></form>
                                        <br /><br /><br />
                                        <?php
                                        if (isset($_GET['vider']) || isset($_GET['supprimer'])) {
                                            if (isset($_GET['confirm']) && $_GET['confirm']=='true') {
                                                $action=isset($_GET['vider'])?'vider':'supprimer';
                                                switch ($action) {
                                                    case 'vider':
                                                        $requete='DELETE FROM numeros WHERE ID_Utilisateur='.$id_user;
                                                        $d->requete($requete);
                                                        echo NUMEROS_SUPPRIMES.'.<br />';
                                                        break;
                                                    case 'supprimer':
                                                        $requete='DELETE FROM numeros WHERE ID_Utilisateur='.$id_user;
                                                        $d->requete($requete);
                                                        echo NUMEROS_SUPPRIMES.'<br />';
                                                        $requete_compte='DELETE FROM users WHERE ID='.$id_user;
                                                        $d->requete($requete_compte);
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
                                        ?>
                                        <?=POSSESSION_MAGAZINES_1?><br /><?=POSSESSION_MAGAZINES_2?><br />
                                        <?php
                                        //echo '<table border="0" width="20%">';
                                        $onglets_magazines=$l->liste_magazines();
                                        $onglets_magazines[NOUVEAU_MAGAZINE]=array('new',AJOUTER_MAGAZINE);
                                        //echo '<span id="onglets_magazines">';
                                        Affichage::onglets($onglet_magazine,$onglets_magazines,'onglet_magazine','?action=gerer&amp;onglet=ajout_suppr');

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
                                            ?>
                                            <table width="100%">
                                            <tr><td>
                                            <?php
                                            if (isset($onglet_magazine) && isset($pays)) {
                                                ?>
                                                <span id="liste_numeros"><?=CHARGEMENT.'...'?></span>
                                                </td><td>
                                                <?php
                                            }
                                            ?>
                                            </td></tr></table>
                                            <?php
                                        }
                                        break;
                                    case 'acquisitions':
                                        ?>
                                        <?=INTRO_ACQUISITIONS1?><br />
                                        <?=INTRO_ACQUISITIONS2?><br /><br />
                                        <table border="0" cellspacing="2px">
                                            <tr>
                                                <td>
                                                    <span id="liste_acquisitions">
                                                    <?php
                                                    Affichage::afficher_acquisitions(false);
                                                    ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span id="nouvelle_acquisition"></span>
                                                </td>
                                            </tr>
                                        </table>
                                        <?php
                                        break;
                                }

                                break;
                            case 'stats':
                                $d=new Database();
                                if (!$d) {
                                    echo PROBLEME_BD;
                                    exit(-1);
                                }
                                ?>
                                <h2><?=STATISTIQUES_COLLECTION?></h2><br />
                                <?php
                                $id_user=$d->user_to_id($_SESSION['user']);
                                $l=$d->toList($id_user);
                                if (!isset($_GET['onglet']))
                                    $onglet='magazines';
                                else
                                    $onglet=$_GET['onglet'];
                                $l->statistiques($onglet);
                                break;

                            case 'print':
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
                                foreach($types_listes as $type) {
                                    if ($type=='Series') continue;
                                    $objet =new $type();
                                    ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if ($type=='DMtable') {
                                                ?>
                                                <iframe height="200px" width="400px" src="Liste.class.php?liste_exemple=true&amp;type_liste=<?=$type?>"></iframe>
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
                                $d=new Database();
                                if (!$d) {
                                    echo PROBLEME_BD;
                                    exit(-1);
                                }
                                $id_user=$d->user_to_id($_SESSION['user']);
                                $l=$d->toList($id_user);

                                $onglets=array(ACHAT_VENTE_NUMEROS=>array('achat_vente',CONTACT_UTILISATEURS),
                                               AUTEURS_FAVORIS=>array('auteurs_favoris',AUTEURS_FAVORIS_TEXTE),
                                               COMPLETER_SERIES=>array('completer_series',COMPLETER_SERIES_TEXTE),
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
                                        $accepte=$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        if ($accepte[0]['AccepterPartage']==0) {
                                            echo COMMENT_PARTAGER_COLLECTION;
                                            ?>
                                            <i><a href="?action=gerer&amp;onglet=options"><?=PAGE_OPTIONS?></a></i>
                                            <?php
                                        }
                                        $d->liste_numeros_externes_dispos($id_user);
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
                                                $d=new Database();
                                                $id_user=$d->user_to_id($_SESSION['user']);
                                                $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
                                                $resultat_auteurs_surveilles=$d->requete_select($requete_auteurs_surveilles);
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
                                                $d->liste_suggestions_magazines();
                                                break;
                                            case 'preferences':
                                                $d=new Database();
                                                $id_user=$d->user_to_id($_SESSION['user']);
                                                if (isset($_POST['auteur_nom'])) {
                                                    $d->ajouter_auteur($_POST['auteur_id'],$_POST['auteur_nom']);
                                                }
                                                ?>
                                                <br /><br />
                                                <?=AUTEURS_FAVORIS_INTRO_1?>
                                                <br />
                                                <?=STATISTIQUES_AUTEURS_INTRO_2?>
                        <br /><br />
                        <form method="post" action="?action=agrandir&amp;onglet=auteurs_favoris&amp;onglet_auteur=preferences">
                            <input type="text" name="auteur_cherche" id="auteur_cherche" value="" />
                            <div class="update" id="liste_auteurs"></div>
                            <input type="hidden" id="auteur_nom" name="auteur_nom" />
                            <input type="hidden" id="auteur_id" name="auteur_id" />
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
                                                        $d->requete($requete_update_recommandations_liste_mags);
                                                    }
                                                    $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
                                                    $resultat_auteurs_surveilles=$d->requete_select($requete_auteurs_surveilles);
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
                                                                    $d->requete($requete_notation);
                                                                }
                                                            }
                                                            $i++;
                                                        }
                                                    }
                                                    $resultat_auteurs_surveilles=$d->requete_select($requete_auteurs_surveilles);
                                                    $d->liste_auteurs_surveilles($resultat_auteurs_surveilles,true);
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
                                        $d=new Database();
                                        if (!$d) {
                                            echo PROBLEME_BD;
                                            exit(-1);
                                        }

                                        if (isset($_POST['ajouter'])) {
                                            $requete='INSERT INTO bouquineries(Nom, Adresse, CodePostal, Ville, Pays, Commentaire, ID_Utilisateur) VALUES (\''.$_POST['nom'].'\',\''.$_POST['adresse'].'\',\''.$_POST['cp'].'\',\''.$_POST['ville'].'\',\'France\',\''.$_POST['commentaire'].'\','.$id_user.')';
                                            ?>
                                            <span style="color:red">
                                            <?php
                                            if ($id_user==1)
                                                $d->requete($requete);
                                            else {
                                                mail('perel.bruno@wanadoo.fr','Ajout de bouquinerie',$requete);
                                                echo EMAIL_ENVOYE;
                                            }
                                            echo MERCI_CONTRIBUTION;
                                            ?>
                                            </span><br />
                                            <?php
                                        }
                                        ?>
                                        <h2><?=LISTE_BOUQUINERIES?></h2>
                                        <?php
                                        $requete_bouquineries='SELECT Nom, Adresse, CodePostal, Ville, Pays,ID_Utilisateur, Commentaire, username FROM bouquineries '
                                                .'INNER JOIN users ON bouquineries.ID_Utilisateur=users.ID '
                                                .'ORDER BY Pays, CodePostal, Ville';
                                        $resultat_bouquineries=$d->requete_select($requete_bouquineries);
                                        $pays='';
                                        $departement='';
                                        $ville='';
                                        foreach($resultat_bouquineries as $bouquinerie) {
                                            $departement_courant=substr($bouquinerie['CodePostal'],0,2);
                                            if ($pays!=$bouquinerie['Pays']) {
                                                ?><h3><?=$bouquinerie['Pays']?></h3><?php
                                            }
                                            if ($departement!=$departement_courant) {
                                                ?><h4><?=DEPARTEMENT.$departement_courant?></h4><?php
                                            }
                                            if ($ville!=$bouquinerie['Ville']) {
                                                ?><h5><?=$bouquinerie['Ville']?></h5><?php
                                            }
                                            ?>
                                            <div style="cursor:help" title="<?=$bouquinerie['Commentaire']?>">
                                            <b><?=$bouquinerie['Nom']?></b> :
                                               <?=$bouquinerie['Adresse']?>,<?=$bouquinerie['CodePostal']?> <?=$bouquinerie['Ville']?>
                                                    <i>(<?=PROPOSE_PAR.$bouquinerie['username']?>)</i></div><br />
                                            <?php
                                            $pays=$bouquinerie['Pays'];
                                            $departement=$departement_courant;
                                            $ville=$bouquinerie['Ville'];
                                        }
                                        ?>
                                        <br /><br />
                                        <?php
                                        $id_user=$d->user_to_id($_SESSION['user']);
                                        ?>
                                        <h2><?=PROPOSER_BOUQUINERIE?></h2>
                                        <?=PRESENTATION_BOUQUINERIE1?><br />
                                        <?=INTRO_NOUVELLE_BOUQUINERIE?><br />
                                        <?=PRIX_HONNETES?>
                                        <br /><br />
                                        <form method="post" action="?action=agrandir&amp;onglet=bouquineries">
                                            <table border="0">';
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
                                <?=PRESENTATION1?><br /><br />
                                <?=PRESENTATION2?><br /><br /><br />
                                <table>
                                    <tr>
                                        <td width="500"><img alt="demo 4" src="images/demo4.png" /></td>
                                        <td valign="middle">
                                            <b><?=PRESENTATION_GERER_TITRE?></b>
                                            <br /><br />
                                            <?=PRESENTATION_GERER_1?>
                                            <br /><br />
                                            <?=PRESENTATION_GERER_2?>
                                            <br /><br />
                                            <?=PRESENTATION_GERER_3?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="middle">
                                            <b><?=PRESENTATION_STATS_TITRE?></b>
                                            <br /><br />
                                            <?=PRESENTATION_STATS_1?>
                                            <br /><br />
                                            <?=PRESENTATION_STATS_2?>
                                            <br /><br />
                                            <?=PRESENTATION_STATS_3?>
                                            <br /><br /><br />
                                            <span style="color:red"><?=NOUVEAU?></span><?=ANNONCE_AGRANDIR_COLLECTION1?>
                                            <br /><br /><br /><br />
                                            <div style="border:1px solid white;text-align:center;">
                                                <?=PRESENTATION_GENERALE?>.<br />
                                                <h3><?=BIENVENUE?></h3>
                                            </div>
                                        </td>
                                        <td><img width="350" alt="demo 1-2-3" src="images/demo123.png" /></td>
                                    </tr>
                                </table>
                                <br />
                                <?=GRATUIT_AUCUNE_LIMITE?> <a href="?action=new"><?=INSCRIVEZ_VOUS?></a>
                                <?php
                                break;
                        }
                        fin_de_page();

                        function afficher_form_inducks() {
                            echo ENTREZ_IDENTIFIANTS_INDUCKS;
                            ?>
                            <br /><br />
                            <?php
                            if (!isset($_SESSION['user'])) {
                                ?><span style="color:red"><?=ATTENTION_MOT_DE_PASSE_INDUCKS?></span><br /><?php
                            }
                            ?>
                            <form method="post" action="index.php?action=import">
                                <table border="0">
                                    <tr>
                                        <td><?=UTILISATEUR_INDUCKS?> :</td>
                                        <td><input type="text" name="user" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?=MOT_DE_PASSE_INDUCKS?> :
                                        </td>
                                        <td>
                                            <input type="password" name="pass" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" colspan="2">
                                            <input type="submit" value="<?=CONNEXION?>"/>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                            <?php
                        }

                        function appel() {
                            $user_pcent=rawurlencode($_POST['user']);
                            $user_urled=urlencode($_POST['user']);
                            $pass_pcent=rawurlencode($_POST['pass']);
                            $pass_urled=urlencode($_POST['pass']);
                            $data = urlencode('login='.$user_pcent.'&pass='.$pass_pcent.'&redirect=collection.php');

                            $fd = fsockopen( gethostbyname('coa.inducks.org'), 80 );
                            return '\''.$_POST['user'].'\',\''.$_POST['pass'].'\',\''.$user_pcent.'\',\''.$user_urled.'\',\''.$pass_pcent.'\',\''.$pass_urled.'\',\''.$data.'\',\'POST\',\'http://coa.inducks.org/collection.php\',\'coa-preferred-language=4\',\'coa.inducks.org\'';
                        }

                        function fin_de_page() {
                            ?>
                    </div>
                </td>

            </tr>
            <tr style="height:3px;background-color:black;"><td colspan="5"></td></tr>
            <tr style="height:20px">
                <td align="center" style="padding-left:4px;width: 242px;">
                        <?php
                        $d=new Database();
                        if (!$d) {
                            echo PROBLEME_BD;
                            exit(-1);
                        }
                        $resultat_cpt_users=$d->requete_select('SELECT count(username) as cpt_users FROM users');
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
?>