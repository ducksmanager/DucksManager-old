<?php header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
require_once('_priv/Admin.priv.class.php');
require_once('travaux.php');
require_once('DucksManager_Core.class.php');
require_once('Liste.class.php');
require_once('JS.class.php');
require_once('Menu.class.php');
require_once('Affichage.class.php');
require_once('Inducks.class.php');
require_once('Util.class.php');
if (Util::isLocalHost() || isset($_GET['dbg'])) {
	error_reporting(E_ALL);
}
else  {
	error_reporting(E_STRICT | E_WARNING);
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
	setCookie('user','',time()-3600);
	setCookie('pass','',time()-3600);
}
else {
	if (isset($_SESSION['user']) && isset($_SESSION['pass']) && !isset($_COOKIE['user']) ) {
		setCookie('user',$_SESSION['user'],time()+3600);
		setCookie('pass',$_SESSION['pass'],time()+3600);
	}
	if (isset($_COOKIE['user']) && isset($_COOKIE['pass'])) {
		if (!DM_Core::$d->user_connects($_COOKIE['user'],$_COOKIE['pass'])) {
			$_SESSION['user']=$_COOKIE['user'];
			setCookie('user',$_COOKIE['user'],time()+3600); // On met les 2 cookies à jour à chaque rafraichissement
			setCookie('pass',sha1($_COOKIE['pass']),time()+3600);
		}
	}
}

$action=isset($_GET['action'])?$_GET['action']:null;
if (defined('TITRE_PAGE_'.strtoupper($action)))
    $titre=constant('TITRE_PAGE_'.strtoupper($action));
else
    $titre=constant('TITRE_PAGE_ACCUEIL');
$id_user=isset($_SESSION['user']) ? DM_Core::$d->user_to_id($_SESSION['user']) : null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Cache-Control" CONTENT="no-store" />
        <meta http-equiv="Expires" content="0" />
        <meta name="keywords" content="collection,bandes dessin&eacute;es,disney,biblioth&egrave;que,statistiques,revues,magazines,inducks,gestion,bouquineries,don rosa,barks,picsou,donald,mickey,comics,bookcase,issues" />
        <title><?php echo TITRE.' - '.$titre;?></title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <!--[if IE]>
              <style type="text/css" media="all">@import "fix-ie.css";</style>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="scriptaculous.css">
        <link rel="stylesheet" type="text/css" href="autocompleter.css">
        <link rel="stylesheet" type="text/css" href="csstabs.css">
        <link rel="stylesheet" type="text/css" href="bibliotheque.css">
        <link rel="stylesheet" type="text/css" href="pluit-carousel.css">
        <link rel="stylesheet" type="text/css" href="pluit-carousel-skins.css">
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
        new JS('prototype.js','js/scriptaculous/src/scriptaculous.js','js/pluit-carousel.js','js/my_scriptaculous.js','js/l10n.js','js/ajax.js');
        if (!is_null($action)) {
            new JS('js/sel_num.js');
			if (!isset($_GET['action'])) $_GET['action']='';            
			switch($_GET['action']) {
                case 'gerer':
                    new JS('js/edges2.js','js/menu_contextuel.js');
                break;  
                case 'bibliotheque':
                    $textures=array();
                    for ($i=1;$i<=2;$i++) {
                        $requete_textures='SELECT Bibliotheque_Texture'.$i.', Bibliotheque_Sous_Texture'.$i.' FROM users WHERE ID = \''.$id_user.'\'';
                        $resultat_textures=DM_Core::$d->requete_select($requete_textures);
                        $textures[]=$resultat_textures[0]['Bibliotheque_Texture'.$i];
                        $textures[]=$resultat_textures[0]['Bibliotheque_Sous_Texture'.$i];
                    }
                    ?>
                    <script type="text/javascript">
                        var texture1='<?=$textures[0]?>';
                        var sous_texture1='<?=$textures[1]?>';
                        var texture2='<?=$textures[2]?>';
                        var sous_texture2='<?=$textures[3]?>';
                    </script><?php
                    new JS('js/edges2.js');
                break;
                case 'stats':if (!isset($_GET['onglet'])) $_GET['onglet']='magazines';
                    switch($_GET['onglet']) {
                        case 'possessions':
                            new JS('js/chargement.js','js/classement_histogramme.js','js/json/json2.js','js/swfobject.js');
                        break;
                    }
                break;
            }
            new JS('js/selection_menu.js','js/bouquineries.js','js/divers.js');
        }
        ?>
    </head>

    <?php
    $texte_debut='';
    if ($action=='demo') {
    	$action='open';
    	$_POST['user']='demo';
    	$_POST['pass']='demodemo';
    }
    if ($action=='open'&& isset($_POST['user'])) {
        if (!DM_Core::$d->user_connects($_POST['user'],$_POST['pass']))
            $texte_debut.= 'Identifiants invalides!<br /><br />';
        else {
            creer_id_session($_POST['user'],$_POST['pass']);
        }
    }
    else {
        
    }
    ?>
    <body id="body" style="margin:0" onload="<?php
    switch($action) {
        case 'open':
            echo 'defiler_log(\'DucksManager\');';
            break;
        case 'bibliotheque':
            if (!isset($_GET['onglet']) || $_GET['onglet']=='affichage') {
                if (Util::getBrowser()!=='MSIE<9') {
                    $requete_grossissement='SELECT Bibliotheque_Grossissement FROM users WHERE ID = \''.$id_user.'\'';
                    $resultat_grossissement=DM_Core::$d->requete_select($requete_grossissement);
                    $grossissement=$resultat_grossissement[0]['Bibliotheque_Grossissement'];
                    $regen=isset($_GET['regen']) ? 1 : 0;
                    echo 'charger_bibliotheque(\''.$grossissement.'\','.$regen.');';
                }
            }
            elseif (isset($_GET['onglet']) && $_GET['onglet']=='options') {
                echo 'initTextures();init_ordre_magazines()';
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
                    echo 'initPays();charger_recherche();';
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
        case 'stats':
            if (isset($_GET['onglet']) && $_GET['onglet']=='auteurs') {
                echo 'init_autocompleter_auteurs();';
            }
            break;
        case 'agrandir':
            if (isset($_GET['onglet']) && $_GET['onglet']=='auteurs_favoris') {
                echo 'init_autocompleter_auteurs();';
                echo 'init_notations();';
            }
            break;
        default:echo 'defiler_log(\'DucksManager\');';
    }
    ?>">
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
                                        src="rouge.png" alt="X" />&nbsp;<span id="texte_connecte"><?=NON_CONNECTE?></span><br /><br />
                                    <?php }?>
                                </div>
                            </td></tr>
                    </table>
                </td>
                <td colspan="2" id="zone_logo1" style="">
                </td>
            </tr>
            <tr style="height:100%">
                <td style="height: 441px; vertical-align: top; width: 242px; background-color: rgb(200, 137, 100);">
                    <table style="height:100%; width:100%" cellspacing="0"><tbody>
                            <tr>
                                <td id="colonne_gauche" valign="top" style="padding:5px;">
                                    <div>
                                        <b><a href="?"><?=ACCUEIL?></a></b><br />
                                        <?php
                                        $beta_user=DM_Core::$d->user_is_beta();
                                        Menu::$beta_user=$beta_user;
                                        Menu::$action=$action;
                                        Menu::afficherMenus($menus);
                                        ?>
                                        <br/>
                                    </div>
                                    <div id="couverture_preview">
                                    </div>
                                </td>
                            </tr></tbody>
                    </table>
                </td>
                <td colspan="2" id="zone_logo2">
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
                        	if (! isset($menu->items))
                        		continue;
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
                                                    <input type="hidden" name="dbg" value="<?=isset($_GET['dbg']) ? "true":"false"?>" />
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
                                        <a href="?action=mot_de_passe_oublie"><?=MOT_DE_PASSE_OUBLIE?></a>
                                    <?php
                                }
                            break;
                            case 'mot_de_passe_oublie' :
                                if (isset($_POST['champs_remplis'])) {
                                    if (empty($_POST['email'])) {
                                        echo MOT_DE_PASSE_OUBLIE_ERREUR_VIDE.'<br />';
                                    }
                                    else {
                                        $requete_verifier_email='SELECT username,password FROM users WHERE Email = \''.$_POST['email'].'\'';
                                        $resultat_verifier_email=DM_Core::$d->requete_select($requete_verifier_email);
                                        if (count($resultat_verifier_email) ==0) {
                                            echo $_POST['email'].' : '.MOT_DE_PASSE_OUBLIE_ERREUR_EMAIL_INCONNU.'<br />';
                                        }
                                        else {
                                            $entete = "MIME-Version: 1.0\r\n";
                                            $entete .= "Content-type: text/html; charset=iso-8859-1\r\n";
                                            $entete .= "To: ".$resultat_verifier_email[0]['username']." <".$_POST['email'].">\r\n";
                                            $entete .= "From: DucksManager <admin@ducksmanager.net>\r\n";
                                            $contenu_mail='Bonjour '.$resultat_verifier_email[0]['username'].'<br /><br />'
                                                         .'Vous recevez cet e-mail &agrave; la suite de votr demande de r&eacute;cup&eacute;ration de mot de passe sur DucksManager.'
                                                         .'<br /><br />Votre mot de passe est :'.$resultat_verifier_email[0]['password']
                                                         .'<br /><br /><br />A bient&ocirc;t sur DucksManager !<br /><br />Le webmaster';
                                            if (mail($_POST['email'], 'Recuperation de mot de passe DucksManager', $contenu_mail,$entete))
                                                echo MOT_DE_PASSE_OUBLIE_EMAIL_ENVOYE;
                                            else
                                                echo MOT_DE_PASSE_OUBLIE_ERREUR_ENVOI_EMAIL;
                                            break;
                                        }
                                    }
                                }
                                ?><?=MOT_DE_PASSE_OUBLIE_EXPLICATION?><br /><br />
                                <form method="post" action="?action=mot_de_passe_oublie">
                                    <input type="hidden" name="champs_remplis" />
                                    <input type="text" name="email" value="" /><br />
                                    <input type="submit" value="<?=ENVOYER?>" />
                                </form>
                                <?php
                            break;
                            case 'logout':
								session_destroy();
								session_unset();
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
                                        if (Util::getBrowser()==='MSIE<9') {
                                            echo IE_INF_A_9_NON_SUPPORTE;
                                        }
                                        else {
                                            ?>
                                            <span id="chargement_bibliotheque_termine"><?=CHARGEMENT?>..</span>.<br />
                                            <div id="barre_pct_bibliotheque" style="border: 1px solid white; width: 200px;">
                                                <div id="pct_bibliotheque" style="width: 0%; background-color: red;">&nbsp;</div>
                                            </div>
                                            <span id="pcent_visible"></span>
                                            <span id="pourcentage_collection_visible"></span>
                                            <br />
                                            <br />
                                            <div id="recherche_bibliotheque">
                                                <?=RECHERCHER_BIBLIOTHEQUE?><br />
                                                <input type="text" style="width:300px" name="" />
                                                <button style="width: 30px;">OK</button>
                                            </div>
                                            <br /><br />
                                            <div id="bibliotheque" style="width:100%;height:100%"></div>
                                            <?php
                                        }
                                    break;
                                    case 'options':
                                        require_once('Edge.class.php');
                                        if (isset($_POST['texture1'])) {
                                            for ($i=1;$i<=2;$i++) {
                                                $requete_update_texture='UPDATE users SET Bibliotheque_Texture'.$i.'=\''.$_POST['texture'.$i].'\' WHERE id='.$id_user;
                                                DM_Core::$d->requete($requete_update_texture);
                                                $requete_update_sous_texture='UPDATE users SET Bibliotheque_Sous_Texture'.$i.'=\''.$_POST['sous_texture'.$i].'\' WHERE id='.$id_user;
                                                DM_Core::$d->requete($requete_update_sous_texture);
                                            }
                                            $requete_suppr_ordres='DELETE FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user;
                                            DM_Core::$d->requete($requete_suppr_ordres);
                                            foreach($_POST as $index=>$valeur) {
                                                if (strpos($index, 'magazine_')!==false) {
                                                    list($pays,$magazine)=explode('_',  substr($index, strlen('magazine_')));
                                                    $requete_ajout_ordre='INSERT INTO bibliotheque_ordre_magazines(Pays,Magazine,Ordre,ID_Utilisateur) '
                                                                        .'VALUES (\''.$pays.'\',\''.$magazine.'\','.$valeur.','.$id_user.')';
                                                    DM_Core::$d->requete($requete_ajout_ordre);
                                                    
                                                }
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
                                            <br /><br /><br />
                                            <span style="text-decoration:underline"><?=ORDRE_MAGAZINES?> : </span><br />
                                            <?=EXPLICATION_ORDRE_MAGAZINES?><br /><br />
                                            <?php
                                            DM_Core::$d->maintenance_ordre_magazines($id_user);?>
                                            <div id="liste_magazines">
                                                <?php
                                                $requete_ordre_magazines='SELECT Pays, Magazine, Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' ORDER BY Ordre';
                                                $resultat_ordre_magazines=DM_Core::$d->requete_select($requete_ordre_magazines);
                                                $publication_codes=array();
                                                foreach($resultat_ordre_magazines as $magazine) {
													$publication_codes[]=$magazine['Pays'].'/'.$magazine['Magazine'];
												}
												list($noms_pays,$noms_magazines)=Inducks::get_noms_complets($publication_codes);
												foreach($resultat_ordre_magazines as $magazine) {
                                                    $num_ordre=$magazine['Ordre'];
                                                    $nom_pays=$magazine['Pays'];
                                                    $nom_magazine=$magazine['Magazine'];
                                                    $pays_complet = $noms_pays[$nom_pays];
                                                    $magazine_complet = $noms_magazines[$nom_pays.'/'.$nom_magazine];
                                                    ?>
                                                    <div style="margin-top:10px;height:40px;" class="magazine_deplacable" id="<?=$nom_pays?>_<?=$nom_magazine?>">
                                                        <div class="handle" style="float:left;text-align:center;border:1px solid white;width:40px">
                                                            <img alt="<?=$nom_pays?>" src="images/flags/<?=$nom_pays?>.png" />
                                                            <br /><?=$nom_magazine?>
                                                        </div>
                                                        <div style="float:left;margin-left: 5px;margin-top: 7px;">
                                                            <?=$magazine_complet?> (<?=$pays_complet?>)
                                                        </div>
                                                        <input type="hidden" name="magazine_<?=$nom_pays?>_<?=$nom_magazine?>" value="<?=$num_ordre?>" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
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
                                            <br />
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
                                                    mail('admin@ducksmanager.net', 'Proposition d\'aide de '.$_SESSION['user'].' pour la bibliothèque',
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
                                $l=DM_Core::$d->toList($id_user);
                                if (isset($_GET['supprimer_magazine'])) {
                                    list($pays,$magazine)=explode('.',$_GET['supprimer_magazine']);
                                    $l_magazine=$l->sous_liste($pays,$magazine);
                                    $l_magazine->remove_from_database (DM_Core::$d, $id_user);
                                }
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
                                        	if ($_SESSION['user'] == 'demo') {
                                        		echo OPERATION_IMPOSSIBLE_MODE_DEMO.'<br />';
                                        	}
                                        	else {
	                                            echo MODIFICATIONS_OK.'<br />';
	                                            DM_Core::$d->requete('UPDATE users SET AccepterPartage='.($_POST['partage']=='on'?'1':'0').', AfficherVideo='.($_POST['video']=='on'?'1':'0').', '
	                                                                .'Email=\''.$_POST['email'].'\' '
	                                                                .'WHERE ID='.$id_user);
                                        	}
                                        }
                                        $resultat_partage=DM_Core::$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        $resultat_email=DM_Core::$d->requete_select('SELECT Email FROM users WHERE ID='.$id_user);
                                        ?>
                                        <form action="?action=gerer&amp;onglet=compte" method="post">
                                        <br /><?=ADRESSE_EMAIL?> : <br />
                                        <input type="text" name="email" value=<?php
                                        if (is_null($resultat_email[0]['Email'])) {
                                            echo '""';
                                        }
                                        else {
                                            echo '"'.$resultat_email[0]['Email'].'"';
                                        }?> /><br /><br />
                                        <input type="checkbox" name="partage"<?php
                                        if ($resultat_partage[0]['AccepterPartage']==1) {?>checked="checked"<?php } ?>/><?=ACTIVER_PARTAGE?><br />
										
                                        <br />
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
                                        if (isset($_GET['confirm']) && $_SESSION['user'] == 'demo') {
                                        	echo OPERATION_IMPOSSIBLE_MODE_DEMO.'<br /><br />';
                                        	unset($_GET['vider']);
                                        	unset($_GET['supprimer']);
                                        }
                                        if (isset($_GET['vider']) || isset($_GET['supprimer'])) {
                                            if (isset($_GET['confirm']) && $_GET['confirm']=='true') {
                                                if ($_SESSION['user'] != 'demo') {
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
                                            }
                                            else {
                                                ?>
                                                <?=OPERATION_IRREVERSIBLE?><br /><?=CONTINUER_OUI_NON?><br />
                                                <a href="?action=gerer&amp;onglet=compte&amp;<?= isset($_GET['vider'])?'vider':'supprimer'?>=true&amp;confirm=true">
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
                                    	if (DM_Core::$d->est_utilisateur_vendeur_sans_email()) {
                                    		?><div class="warning">
                                    			<?=ATTENTION_VENTE_SANS_EMAIL?>
                                    			<a href="?action=gerer&amp;onglet=compte"><?=GESTION_COMPTE_COURT?></a>.
                                    		</div><?php
                                    	}
                                        if ($_SESSION['user'] == 'demo') {
                                        	require_once('init_demo.php');
											$nb_minutes_avant_reset=60 - strftime('%M',time());
											if ($nb_minutes_avant_reset == 0)
											$nb_minutes_avant_reset=60;
                                        	?><div id="presentation_demo">
                                        		<h2><?=PRESENTATION_DEMO_TITRE?></h2>
                                        		<?=PRESENTATION_DEMO.$nb_minutes_avant_reset.' '.MINUTES?>
                                        	</div><?php
                                        }
                                        $l=DM_Core::$d->toList($id_user);
                                        $nb_numeros=0;
                                        $nb_magazines=$nb_pays=0;
                                        foreach($l->collection as $pays=>$numeros_pays) {
                                            $nb_pays++;
                                            foreach(array_keys($numeros_pays) as $magazine) {
                                                $nb_magazines++;
                                                $nb_numeros+=count($numeros_pays[$magazine]);
                                            }
                                        }
                                        ?>
                                        <?=POSSESSION_MAGAZINES_1?> <?=$nb_numeros?> <?=NUMEROS?>. 
                                        <?=POSSESSION_MAGAZINES_2?> <?=$nb_magazines?> <?=POSSESSION_MAGAZINES_3?> <?=$nb_pays?> <?=PAYS?>.
                                        <br />
                                        <?=POSSESSION_MAGAZINES_4?><br />
                                        <?php
                                        
                                        list($onglets_pays,$onglets_magazines)=$l->liste_magazines();
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
                        <table style="border:0">
                            <tr>
                                <td style="width:400px">
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
                                    </form>
                                </td>
                                <td style="vertical-align:top">
                                    <br />
                                    <?=RECHERCHE_MAGAZINE?>
                                    
                                    <div id="recherche_bibliotheque" style="display:block;margin-top: 0px;">
                                        <input type="text" style="width:300px" name="" />
                                        <button style="width: 30px;">OK</button>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <br /><br />
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
                                $l=DM_Core::$d->toList($id_user);
                                if (!isset($_GET['onglet']))
                                    $onglet='magazines';
                                else
                                    $onglet=$_GET['onglet'];
                                $l->statistiques($onglet);
                                break;

                            case 'print':
                                if (false) {//$_SESSION['user']!='nonoox' && $_SESSION['user']!='brunoperel') {
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
                                $l=DM_Core::$d->toList($id_user);

                                $onglets=array(ACHAT_VENTE_NUMEROS=>array('achat_vente',CONTACT_UTILISATEURS),
                                               AUTEURS_FAVORIS=>array('auteurs_favoris',AUTEURS_FAVORIS_TEXTE));
                                if (!isset($_GET['onglet']))
                                    $onglet='achat_vente';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet,$onglets,'onglet','?action=agrandir');
                                switch($onglet) {
                                    case 'achat_vente':
                                        echo INTRO_ACHAT_VENTE;
                                        ?><br /><?php
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
                                                $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat = \'0000-00-00\'';
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
                                                        
		                                                <br /><br />
		                                                <?php
		                                                DM_Core::$d->liste_suggestions_magazines();
                                                    }
                                                }
                                                else echo AUCUN_AUTEUR_SURVEILLE.' '.AUCUN_AUTEUR_SURVEILLE_CLIQUER_ONGLET;
                                                
                                                break;
                                            case 'preferences':
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
                        <hr />
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
                                                    $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat = \'0000-00-00\'';
                                                    if (isset($_POST['auteur0'])) {
	                                                    $resultat_auteurs_surveilles=DM_Core::$d->requete_select($requete_auteurs_surveilles);
	                                                    foreach($resultat_auteurs_surveilles as $auteur) {
	                                                        $i=0;
	                                                        while (isset($_POST['auteur'.$i])) {
	                                                            if ($_POST['auteur'.$i] == $auteur['NomAuteurAbrege']) {
                                                                    $notation=empty($_POST['notation'.$i]) ? -1 : $_POST['notation'.$i];
                                                                    $requete_notation='UPDATE auteurs_pseudos SET Notation='.$notation.' '
		                                                                             .'WHERE DateStat = \'0000-00-00\' AND NomAuteurAbrege = \''.$_POST['auteur'.$i].'\' '
		                                                                             .'AND ID_user='.$id_user;
                                                                    DM_Core::$d->requete($requete_notation);
	                                                            }
	                                                            $i++;
	                                                        }
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
                                }

                                break;
                                case 'bouquineries':
                                	echo INTRO_BOUQUINERIES.'<br />';
                                	if (isset($_POST['ajouter'])) {
                                		$requete='INSERT INTO bouquineries(Nom, Adresse, CodePostal, Ville, Pays, Commentaire, ID_Utilisateur) VALUES (\''.$_POST['nom'].'\',\''.$_POST['adresse'].'\',\''.$_POST['cp'].'\',\''.$_POST['ville'].'\',\'France\',\''.$_POST['commentaire'].'\','.(is_null($id_user) ? 'NULL':$id_user).')';
                                		?>
								<span style="color: red">
								<?php
								if (!is_null($id_user) && $id_user==1)
									DM_Core::$d->requete($requete);
								else {
									mail('admin@ducksmanager.net','Ajout de bouquinerie',$requete);
									echo EMAIL_ENVOYE.EMAIL_ENVOYE_BOUQUINERIE;
								}
								echo MERCI_CONTRIBUTION;
								?> </span><br />
		
								<?php
		                     }
		                     ?>
							<h2>
							<?=LISTE_BOUQUINERIES?>
							</h2>
							<iframe src="bouquineries.php" width="70%" height="700px"></iframe>
							<br /> <br />
	
							<h2>
							<?=PROPOSER_BOUQUINERIE?>
							</h2>
							<?=PRESENTATION_BOUQUINERIE1?>
							<br />
							<?=INTRO_NOUVELLE_BOUQUINERIE?>
							<br />
							<?=PRIX_HONNETES?>
							<br /> <br />
							<form method="post" action="?action=bouquineries">
								<table border="0">
									<tr>
										<td><?=NOM_BOUQUINERIE?> :</td>
										<td><input maxlength="25" size="26" name="nom" type="text" /></td>
									</tr>
									<tr>
										<td><?=ADRESSE?> :</td>
										<td><textarea cols="20" name="adresse"></textarea></td>
									</tr>
									<tr>
										<td><?=CODE_POSTAL?> :</td>
										<td><input maxlength="11" name="cp" type="text" size="5"
											maxlength="5" /></td>
									</tr>
									<tr>
										<td><?=VILLE?> :</td>
										<td><input maxlength="20" size="26" name="ville" type="text" />
										</td>
									</tr>
									<tr>
										<td><?=COMMENTAIRES_BOUQUINERIE?><br />(<?=COMMENTAIRES_BOUQUINERIE_EXEMPLE?>)</td>
										<td><textarea name="commentaire" colspan="40" rowspan="5"></textarea>
										</td>
									</tr>
									<tr>
										<td align="center" colspan="2">
											<input name="ajouter" type="submit" value="<?=AJOUTER_BOUQUINERIE?>" />
										</td>
									</tr>
	                        	</table>
							</form>
						<?php
						break;
						
						case 'duckhunt_tour':
						?>
						<h2>
						<?=DUCKHUNT_TOUR?>
						</h2>
						<br />
						<?php
						echo TEXTE_DUCKHUNT_TOUR_1.'<br /><br />';
						echo TEXTE_DUCKHUNT_TOUR_2.'<br /><br />';
						echo TEXTE_DUCKHUNT_TOUR_3.'<br /><br />';
						?>
						<hr />
						<h3>
						<?=DUCKHUNT_TOUR_2011_1?>
						</h3>
						<br />

						<?php
						echo TEXTE_DUCKHUNT_TOUR_2011_1.'<br />';
						?>
						<table style="width: 100%; border: 0">
							<tr>
								<td style="width:273px"><img src="images/duckhunt_tour_2011.png" /></td>
								<td>
									<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.fr/maps?saddr=libourne&amp;daddr=saintes+to:la+rochelle+to:Beaulieu-Sous-la-Roche+to:Nantes+to:Saint-Nazaire+to:Angers+to:Avrill%C3%A9+to:Laval+to:Change,+Mayenne,+Pays+de+la+Loire+to:Le+Mans+to:blois+to:tours+to:Montmorillon+to:Poitiers+to:Saint-Maixent-l'%C3%89cole+to:Niort+to:m%C3%A9rignac&amp;hl=fr&amp;ie=UTF8&amp;sll=46.505954,-1.230469&amp;sspn=5.44462,14.27124&amp;geocode=FWZRrQId70b8_yn7BnAWT0lVDTHw4RZIF2UGBA%3BFS8AugId01X2_ylVtrfUV_0ASDEU_3AchsfvVA%3BFclZwAIdXW_u_yl1PSXJg1MBSDFAlu5gktMFBA%3BFRE-yAIdsm3n_ylvCK844DcESDGZXNhevR-9Jg%3BFcN-0AIdK0vo_ymtrqjwge4FSDEw7Q0eUjcNBA%3BFRpW0QIdKDje_ykT78I8j2UFSDE9H35eDebfZA%3BFZN21AIdImj3_ymdjuUA2ngISDEwnA0eUjcNBA%3BFbff1AId2_z2_ykdFBprxn4ISDHvBrnoflBflg%3BFSec3QIdpj_0_ynBa1n86f0ISDHgFg0eUjcNBA%3BFYDt3QIdVPbz_ymrsBfEVQIJSDFwGw0eUjcNBA%3BFd6D3AIdhAsDACkxqvMU0ojiRzF_4H1qTF0CaQ%3BFXAi1gId0GMUACld0sgjlVfjRzGwKTgF18gNBA%3BFWAt0wIdKHMKAClVmpdKs9X8RzEwhDgF18gNBA%3BFUNjxAId0D4NACmLtkm69zv8RzEFV_CQAV3uoQ%3BFQDCxgIdlzEFACmrs55Dcr79RzGTkODFGSPelw%3BFW01xAIdbuX8_ynPRvrIRkQHSDHQJuhgktMFBA%3BFQTYwgIdd-j4_yn1htYRMjAHSDHAKuhgktMFBA%3BFU1HrAIdGvz1_ym3I4C629lUDTF2HoHc9b1EVg&amp;vpsrc=0&amp;mra=ls&amp;t=m&amp;ll=46.475699,-0.428467&amp;spn=5.447645,14.27124&amp;output=embed"></iframe>
								</td>
							</tr>
							<tr>
								<td colspan="2">Pour l'&eacute;dition 2011, 24 bouquineries et
									centres Emma&ucirc;s ont &eacute;t&eacute; recherch&eacute;s.<br />
									Parmi ces 24,
									<ul style="padding-left:20px;">
										<li>19 ont &eacute;t&eacute; trouv&eacute;s.</li>
										<li>15 &eacute;taient ouverts lors de notre passage.</li>
										<li><b>5 se sont r&eacute;v&eacute;l&eacute;s &ecirc;tre des lieux
											&agrave; d&eacute;couvrir pour tout fan de magazines Disney
											qui se respecte, et ont par cons&eacute;quence &eacute;t&eacute; ajout&eacute;s &agrave; la 
											<a href="?action=bouquineries">Carte des bouquineries de DucksManager</a>.</b></li>
									</ul> La m&eacute;daille d'or revient au sympathique
									bouquiniste de Saintes, rue Cl&eacute;menceau, qui regorge de
									vieux Mickey Parade, y compris dans l'arri&egrave;re boutique.<br />
									<br /> Les d&eacute;tails sur chacune des bouquineries : <br />
									<iframe style="width: 90%; height: 500px"
										src="duckhunt_tour_2011.htm"></iframe>
								</td>
							</tr>
						</table>
						<?php
						break;
						
						default:?>
						<div id="carousel-1"
							class="pluit-carousel top-stories-skin">
							<div class="viewport">
								<ul>
									<li><img src="images/montage DucksManager_petit.jpg" alt="logo"
										height="333" width="501" />
									</li>
									<li><img src="images/demo2_2.png" alt="demo2_2" width="500"
										height="333" />
									</li>
									<li><img src="images/demo3.png" alt="demo3" width="500"
										height="333" />
									</li>
									<li><img src="images/demo_bibliotheque.png" alt="demo2_2"
										width="500" height="333" />
									</li>
								</ul>
							</div>
							<!-- Custom Navigation -->
							<ul class="nav">
								<li class="pages">
									<ul>
										<li class="active page-1"><a href="#"><?=BIENVENUE?> </a></li>
										<li class="page-2"><a href="#"><?=PRESENTATION_GERER_TITRE?> </a>
										</li>
										<li class="page-3"><a href="#"><?=PRESENTATION_STATS_TITRE?> </a>
										</li>
										<li class="last page-4"><a href="#"><?=PRESENTATION_BIBLIOTHEQUE_TITRE?>
										</a></li>
									</ul>
								</li>
							</ul>
							<div id="conteneur_infos_fonc">

								<div id="infos-page-1" class="infos-fonc"
									style="margin-top: 100px">
									<?=PRESENTATION1?>
									<br /> <br />
									<?=PRESENTATION2?>
									<br /> <br />
									<?=GRATUIT_AUCUNE_LIMITE?>
									<a href="?action=new"><?=INSCRIVEZ_VOUS?> </a>
								</div>

								<div id="infos-page-2" class="infos-fonc"
									style="display: none; margin-top: 100px">
									<?=PRESENTATION_GERER_1?>
									<br /> <br />
									<?=PRESENTATION_GERER_2?>
									<br /> <br />
									<?=PRESENTATION_GERER_3?>
								</div>

								<div id="infos-page-3" class="infos-fonc" style="display: none">
									<?=PRESENTATION_STATS_1?>
									<br /> <br />
									<?=PRESENTATION_STATS_2?>
									<br /> <br />
									<?=PRESENTATION_STATS_3?>
									<br /> <br /> <img alt="demo 2" src="images/demo2.png" />
								</div>

								<div id="infos-page-4" class="infos-fonc" style="display: none">
									<?=PRESENTATION_BIBLIOTHEQUE_1?>
									<br /> <br />
									<?=PRESENTATION_BIBLIOTHEQUE_2?>
									<br /> <br />
									<?=PRESENTATION_BIBLIOTHEQUE_3?>
									<br /> <img src="images/demo_bibliotheque2.png" alt="demo2_2" />
								</div>

							</div>
						</div>
						<script type="text/javascript">
						    new Pluit.Carousel('#carousel-1', {
						      circular: true
						    });
						  </script>

						<div style="margin-right: 6px; border-top: 1px solid white; border-bottom: 1px solid white; text-align: center;">
							<?=PRESENTATION_GENERALE?>
							.<br />
							<h3>
								<a href="?action=new"><?=INSCRIVEZ_VOUS?> </a>
							</h3>
						</div>
						<div style="width:300px;margin-top:20px;border:1px solid white">
							<a href="https://play.google.com/store/apps/details?id=net.ducksmanager.whattheduck"><img src="images/WhatTheDuck.png" style="float:left;margin-right:12px"/></a>
							<p style="margin-left:10px">
								<?=PUB_WHATTHEDUCK_1?>
								<a href="https://play.google.com/store/apps/details?id=net.ducksmanager.whattheduck"><b>What The Duck</b></a>
								<?=PUB_WHATTHEDUCK_2?>
								<br />
								<?=PUB_WHATTHEDUCK_3?>
							</p>
						</div>
						<br />
						
                                <?php
                                break;
                        }
                        fin_de_page();

                        function fin_de_page() {
                            ?>
					</div>
                </td>

            </tr>
            <tr style="height:3px;background-color:black;"><td colspan="3"></td></tr>
            <tr style="height:20px">
                <td align="center" style="vertical-align:middle;padding-left:4px;width: 242px;">
                        <?php
                        $resultat_cpt_users=DM_Core::$d->requete_select('SELECT count(username) as cpt_users FROM users');
                        echo $resultat_cpt_users[0]['cpt_users'].' '.UTILISATEURS_INSCRITS;
                        ?>
                </td>
                <td align="center">
                    <?=TEXTE_FORUMDESFANS?><a href="http://leforumdesfanspicsou.1fr1.net/ducksmanager-f18/"><?=LIEN_FORUM_DES_FANS?></a>
                    <br /><br />
                    <?=REMERCIEMENT_LOGO?>
                    <br />
                    <?=LICENCE_INDUCKS1?>
                    <a target="_blank" href="http://coa.inducks.org/inducks/COPYING"><?=LICENCE_INDUCKS2?></a>
                    <br />
                    <?=LICENCE_INDUCKS3?>
                </td>
                <td style="vertical-align:top;" align="right">
                	<?php
					foreach(array_keys(Lang::$codes_inducks) as $nom_langue) {
						if(is_file('locales/'.$nom_langue.'.php')) {
							$nouvelle_url = str_replace('&','&amp;',$_SERVER['QUERY_STRING']);
							$nouvelle_url = preg_replace('#\??(?:&amp;)?lang=[a-z]+#u','',$nouvelle_url);
							$nouvelle_url = '?'.(empty($nouvelle_url) ? '' : $nouvelle_url.'&amp;')
										   .'lang='.$nom_langue;
							?>
							<a class="drapeau_langue" href="<?=$nouvelle_url?>">
								<img style="border:0" src="images/<?=$nom_langue?>.jpg" alt="<?=$nom_langue?>"/>
							</a>
							<?php
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
        if (isset($erreur)) {
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
            <tr><td><?=ADRESSE_EMAIL?> : </td><td><input name="email" type="text" value="" /></td></tr>
            <tr><td><?=MOT_DE_PASSE_6_CHAR?> :</td><td><input name="pass" type="password" /></td></tr>
            <tr><td><?=MOT_DE_PASSE_CONF?> :</td><td><input name="pass2" type="password" /></td></tr>
            <tr><td colspan="2"><input type="submit" value="<?=INSCRIPTION?>" /></td></tr></table>
        </form>
        <?php
    }
    else {
        DM_Core::$d->nouveau_user($_POST['user'], $_POST['email'],$_POST['pass']);
        if (isset($_POST['rawData'])) {
            $l = new Liste($_POST['rawData']);
            $l->add_to_database(DM_Core::$d, DM_Core::$d->user_to_id($_POST['user']));
        }
        creer_id_session($_POST['user'], $_POST['pass']);
    }
}

function creer_id_session($user,$pass) {
    $_SESSION['user']=$user;
    $_SESSION['pass']=sha1($pass);
    echo '<script language="Javascript">
    <!--
    document.location.replace("index.php?action=gerer");
    // -->
    </script>';
}
?>