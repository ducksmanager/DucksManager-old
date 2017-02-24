<?php header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le pass�
date_default_timezone_set('Europe/Paris');
require_once('_priv/Admin.priv.class.php');
require_once('travaux.php');
require_once('DucksManager_Core.class.php');
require_once('Liste.class.php');
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
	setcookie('user','',time()-3600);
	setcookie('pass','',time()-3600);
}
else {
	if (isset($_SESSION['user']) && isset($_SESSION['pass']) && !isset($_COOKIE['user']) ) {
		setcookie('user',$_SESSION['user'],time()+3600);
		setcookie('pass',$_SESSION['pass'],time()+3600);
	}
	if (isset($_COOKIE['user']) && isset($_COOKIE['pass'])) {
		if (!DM_Core::$d->user_connects($_COOKIE['user'],$_COOKIE['pass'])) {
			$_SESSION['user']=$_COOKIE['user'];
			setcookie('user',$_COOKIE['user'],time()+3600); // On met les 2 cookies � jour � chaque rafraichissement
			setcookie('pass',$_COOKIE['pass'],time()+3600);
		}
	}
}

$action=isset($_GET['action'])?$_GET['action']:null;
if (defined('TITRE_PAGE_'.strtoupper($action)))
    $titre=constant('TITRE_PAGE_'.strtoupper($action));
else
    $titre=TITRE_PAGE_ACCUEIL;
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
        <title><?=$titre.' - DucksManager'?></title>
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
        <link rel="stylesheet" type="text/css" href="css/opentip.css">
        <link rel="stylesheet" type="text/css" href="css/stats.css">
        <link rel="stylesheet" type="text/css" href="css/starbox.css" />
        <link rel="stylesheet" href="protomenu.css" type="text/css" media="screen">
        <link rel="icon" type="image/png" href="favicon.png">
        <?php include_once('ServeurDb.class.php');
        if (!isLocalHost()) {?>
            <!-- Piwik -->
            <script type="text/javascript">
            var pkBaseURL = ((("https:" == document.location.protocol) ? "https://" : "http://")+"<?=ServeurDb::getPiwikServer()->domain?>/piwik/");
            document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
            </script>
            <script type="text/javascript">
            try {
            var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
            piwikTracker.setCustomVariable(1, "Utilisateur", "<?=$_SESSION['user']?>", "visit"); 
            piwikTracker.trackPageView();
            piwikTracker.enableLinkTracking();
            } catch( err ) {}
            </script>
            <!-- End Piwik Tag -->
        <?php
        }?>
        <script type="text/javascript">
            var debug=<?=isset($_GET['debug']) ? 'true':'false'?>;
        </script>

        <!-- Bootstrap -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/bootstrap_override.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

        <script type="text/javascript" src="prototype-1.7.3.js"></script>
        <script>
            (function() {
                var isBootstrapEvent = false;
                if (window.jQuery) {
                    var all = jQuery('*');
                    jQuery.each(['hide.bs.dropdown',
                        'hide.bs.collapse',
                        'hide.bs.modal',
                        'hide.bs.tooltip',
                        'hide.bs.popover',
                        'hide.bs.tab'], function(index, eventName) {
                        all.on(eventName, function( event ) {
                            isBootstrapEvent = true;
                        });
                    });
                }
                var originalHide = Element.hide;
                Element.addMethods({
                    hide: function(element) {
                        if(isBootstrapEvent) {
                            isBootstrapEvent = false;
                            return element;
                        }
                        return originalHide(element);
                    }
                });
            })();
        </script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.3.0/Chart.js"></script>
        <script type="text/javascript" src="js/scriptaculous/src/scriptaculous.js"></script>
        <script type='text/javascript' src='js/starbox.js'></script>
        <script type="text/javascript" src="js/pluit-carousel.js"></script>
        <script type="text/javascript" src="js/my_scriptaculous.js"></script>
        <script type="text/javascript" src="js/l10n.js"></script>
        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js/opentip/opentip-prototype-excanvas.min.js"></script>
        <script type="text/javascript" src="js/moment.min.js"></script>
        <script type="text/javascript" src="js/edges2.js"></script><?php

        if (!is_null($action)) {
            ?><script type="text/javascript" src="js/sel_num.js"></script><?php
			if (!isset($_GET['action'])) $_GET['action']='';            
			switch($_GET['action']) {
                case 'gerer':
                    ?><script type="text/javascript" src="js/menu_contextuel.js"></script><?php
                break;
                case 'bouquineries':
                    ?>
                    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
                    <?php
                break;
                case 'bibliotheque':
                    if (isset($_GET['user'])) {
                        $user_bibliotheque = $_GET['user'];
                        $cle_bibliotheque = isset($_GET['key']) ? $_GET['key'] : -1;
                        $est_partage_bibliotheque = true;
                    }
                    else {
                        $user_bibliotheque = -1;
                        $cle_bibliotheque = -1;
                        $est_partage_bibliotheque = false;
                    }
                    $onglet = isset($_GET['onglet']) && in_array($_GET['onglet'], ['affichage', 'options'])
                        ? $_GET['onglet']
                        : 'affichage';
                    $regen = isset($_GET['regen']) ? 1 : 0;
                    ?>
                    <script type="text/javascript">
                        var user_bibliotheque = <?=is_null($user_bibliotheque) ? -1 : "'".$user_bibliotheque."'"?>;
                        var cle_bibliotheque = <?=is_null($cle_bibliotheque) ? -1 : "'".$cle_bibliotheque."'"?>;
                        var est_partage_bibliotheque = <?=$est_partage_bibliotheque ? 1 : 0?>;
                        var onglet = '<?=$onglet?>';
                        var regen = '<?=$regen?>';
                    </script><?php
                break;
                case 'stats':
                    if (!isset($_GET['onglet'])) {
                        $_GET['onglet']='magazines';
                    }
                    ?><script type="text/javascript" src="js/stats.js"></script><?php

                    switch($_GET['onglet']) {
                        case 'possessions': ?>
                            <script type="text/javascript" src="js/chargement.js"></script>
                        <?php
                        break;
                    }
                break;
                case 'agrandir':
                    ?><script type="text/javascript" src="js/stats.js"></script><?php
            }
            ?><script src="js/divers.js"></script><?php
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
    <body id="body" style="margin:0" onload="charger_evenements();<?php
    switch($action) {
        case 'open':
            break;
        case 'bibliotheque':
            switch ($onglet) {
                case 'affichage':
                    if (Util::getBrowser() !== 'MSIE<9') {
                        ?>charger_bibliotheque();<?php
                    }
                break;
                case 'options':
                    ?>initTextures();
                    init_ordre_magazines();<?php
                break;

            }
        break;
        case 'gerer':
            if (isset($_GET['onglet_magazine'])) {
                $onglet_magazine=$_GET['onglet_magazine'];
                if ($onglet_magazine=='new') {
                    ?>initPays(false,'fr');<?php
                }
                else {
                    list($pays,$magazine)=explode('/',$onglet_magazine);
                    ?>afficher_numeros('<?=$pays?>','<?=$magazine?>');<?php
                }
            }
            ?>charger_recherche();<?php
            break;
        case 'bouquineries':
            ?>initializeAutocomplete();<?php
        break;
        case 'stats':
            if (isset($_GET['onglet'])) {
            	switch($_GET['onglet']) {
					case 'auteurs':
						?>
                        init_autocompleter_auteurs();
                        init_notations();
                        afficher_histogramme_stats_auteurs();
                        <?php
					break;
					case 'achats':
						?>afficher_histogramme_achats();<?php
					break;
					case 'magazines':
						?>afficher_diagramme_secteurs('publications');<?php
					break;
					case 'etats':
						?>afficher_diagramme_secteurs('conditions');<?php
					break;
				}
            }
            break;
        case 'agrandir':
            ?>
            initPays(true, '<?=empty($_GET['pays']) ? 'ALL' : $_GET['pays']?>');<?php
            break;
    }
    ?>">
    <table
        style="text-align: left; color: white; background-color: rgb(61, 75, 95); width: 100%; height: 100%;border:0"
        cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td id="medailles_et_login" align="center" style="vertical-align: bottom; height:45px;padding-left:3px;background-color:rgb(61, 75, 95)">
                    <?php
                    if (isset($_SESSION['user']) &&!($action=='logout')) {
						?><div style="white-space: nowrap"><?php						
						$niveaux=DM_Core::$d->get_niveaux();
						foreach($niveaux as $type=>$cpt_et_niveau) {
							if (!is_null($cpt_et_niveau)) {
								$cpt=$cpt_et_niveau['Cpt'];
								$niveau=$cpt_et_niveau['Niveau'];
								?><img class="medaille" src="images/medailles/<?=$type?>_<?=$niveau?>_<?=$_SESSION['lang']?>.png" title="<?php
									echo constant('DETAILS_MEDAILLE_'.strtoupper($type).'_1')
										.' '.$cpt.' '
										.constant('DETAILS_MEDAILLE_'.strtoupper($type).'_2');
								?>"/><?php
							}
						}
                    	?>
                    	</div><br />
                    	<div id="login">
	                    	<img id="light" src="vert.png" alt="O" />&nbsp;
	                    	<span id="texte_connecte"><?=$_SESSION['user']?></span>
	                    </div><?php 
                    } else {
                   		?><img id="light" src="rouge.png" alt="X" />&nbsp;<span id="texte_connecte"><?=NON_CONNECTE?></span><br /><br /><?php 
                    }?>
                </td>
                <td colspan="2" id="zone_logo1" style="">
                </td>
            </tr>
            <tr style="height:100%">
                <td style="height: 441px; vertical-align: top; width: 300px; background-color: rgb(200, 137, 100);">
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
                                    
									<div id="recemment">
										<h4><?=NEWS_TITRE?></h4>
										<div id="evenements"><?=CHARGEMENT?>...</div>
									</div>
                                    <div id="couverture_preview">
                                    	<div class="fermer cache"><?=FERMER?></div>
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
                        if (isset($_SESSION['user']) && $action !== 'logout' && !Inducks::connexion_ok()) {
							?><div class="error"><?=COA_KO_1?><br /><?=COA_KO_2?></div><?php
							fin_de_page();
						}
                        foreach($menus as $i=>$menu) {
                        	if (! isset($menu->items))
                        		continue;
                            foreach($menu->items as $j=>$item) {
                                if ($item->nom==$action) {
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
                                    	$ajouter_numeros_inducks=isset($_POST['ajouter_numeros']);
                                        $supprimer_numeros_inducks=isset($_POST['supprimer_numeros']);
                                        $l=new Liste($_POST['rawData']);
                                        $l->synchro_to_database($ajouter_numeros_inducks,$supprimer_numeros_inducks);   
                                    }
                                    else {
                                        $rawdata_valide= Inducks::liste_numeros_valide($_POST['rawData']);
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
                                    echo IMPORT_INDUCKS_DESACTIVE;
                                    /*
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
                                <?php */
                                }
                            break;
                            case 'new':
                                formulaire_inscription();
                                break;
                            case 'open':
                                if (!isset($_SESSION['user'])) {
                                    ?>
                                    <?=IDENTIFIEZ_VOUS?><br /><br />
                                    <form method="post" action="?action=open">
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
										$requete_verifier_email='SELECT username, Email FROM users WHERE Email = \''.$_POST['email'].'\'';
										$resultat_verifier_email=DM_Core::$d->requete_select($requete_verifier_email);
                                        if (count($resultat_verifier_email) ==0) {
                                            echo $_POST['email'].' : '.MOT_DE_PASSE_OUBLIE_ERREUR_EMAIL_INCONNU.'<br />';
                                        }
                                        else {
											$chars = "abcdefghijkmnopqrstuvwxyz023456789";
											srand((double)microtime()*1000000);
											$mdp_temporaire = '' ;
											for ($i = 0 ; $i <= 28 ; $i++) {
												$num = rand() % 33;
												$tmp = substr($chars, $num, 1);
												$mdp_temporaire = $mdp_temporaire . $tmp;
												$i++;
											}
											
											$requete_maj_mdp='UPDATE users SET password=sha1(\''.$mdp_temporaire.'\') WHERE Email = \''.$_POST['email'].'\'';
											DM_Core::$d->requete($requete_maj_mdp);
                                            
											$entete = "MIME-Version: 1.0\r\n";
                                            $entete .= "Content-type: text/html; charset=iso-8859-1\r\n";
                                            $entete .= "To: ".$resultat_verifier_email[0]['username']." <".$_POST['email'].">\r\n";
                                            $entete .= "From: DucksManager <admin@ducksmanager.net>\r\n";
                                            $contenu_mail=
                                                sprintf(SALUTATION,$resultat_verifier_email[0]['username'])
                                                .'<br /><br />'.EMAIL_REINIT_MOT_DE_PASSE_1
                                                .'<br /><br />'.EMAIL_REINIT_MOT_DE_PASSE_2.$mdp_temporaire
                                                .'<br /><br /><br />'.EMAIL_REINIT_MOT_DE_PASSE_3
                                                .'<br /><br />'.EMAIL_SIGNATURE;
                                            if (mail($_POST['email'], EMAIL_REINIT_MOT_DE_PASSE_TITRE, $contenu_mail,$entete))
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
                                <div id="conteneur_bibliotheque">
                                    <h2 id="titre_bibliotheque"></h2><br /><br />
                                <?php
                                if (!$est_partage_bibliotheque) {
                                    $onglets = [
                                        BIBLIOTHEQUE_COURT => ['affichage', BIBLIOTHEQUE],
                                        BIBLIOTHEQUE_OPTIONS_COURT => ['options', BIBLIOTHEQUE_OPTIONS],
                                        BIBLIOTHEQUE_PARTICIPER_COURT => ['participer', BIBLIOTHEQUE_PARTICIPER],
                                        BIBLIOTHEQUE_CONTRIBUTEURS_COURT => ['contributeurs', BIBLIOTHEQUE_CONTRIBUTEURS]];
                                    if (!isset($_GET['onglet']))
                                        $onglet = 'affichage';
                                    else
                                        $onglet = $_GET['onglet'];
                                    Affichage::onglets($onglet, $onglets, 'onglet', '?action=bibliotheque');
                                }
                                switch ($onglet) {
                                    case 'affichage':
                                        if (Util::getBrowser() === 'MSIE<9') {
                                            echo IE_INF_A_9_NON_SUPPORTE;
                                        } else {
                                            if (!$est_partage_bibliotheque) {
                                                $resultat_tranches_collection_ajoutees = DM_Core::$d->get_tranches_collection_ajoutees($id_user, false);
                                                if (count($resultat_tranches_collection_ajoutees) > 0) {
                                                    $publication_codes = [];
                                                    foreach ($resultat_tranches_collection_ajoutees as $tranche) {
                                                        $publication_codes[] = $tranche['publicationcode'];
                                                    }
                                                    $publication_codes = array_unique($publication_codes);
                                                    list($pays_complets, $magazines_complets) = Inducks::get_noms_complets($publication_codes);

                                                    ?>
                                                    <div>
                                                        <?= BIBLIOTHEQUE_NOUVELLES_TRANCHES_LISTE ?><br/>
                                                        <?php
                                                        foreach ($resultat_tranches_collection_ajoutees as $tranche) {
                                                            list($pays, $magazine) = explode('/', $tranche['publicationcode']);
                                                            echo Affichage::afficher_texte_numero($pays, $magazines_complets[$tranche['publicationcode']], $tranche['issuenumber'])
                                                                . Affichage::afficher_temps_passe($tranche['DiffSecondes']) . '<br />';
                                                        }
                                                        ?>
                                                    </div><br/><br/><?php
                                                }
                                            }
                                            ?>
                                            <span id="chargement_bibliotheque_termine"><?= CHARGEMENT ?>..</span>.
                                            <br/>
                                            <div id="barre_pct_bibliotheque"
                                                 style="border: 1px solid white; width: 200px;">
                                                <div id="pct_bibliotheque"
                                                     style="width: 0; background-color: red;">&nbsp;</div>
                                            </div>
                                            <span id="pcent_visible"></span>
                                            <span id="pourcentage_collection_visible"></span>
                                            <br/>
                                            <br/>
                                            <?php if (!$est_partage_bibliotheque) { ?>
                                                <div id="partager_bibliotheque" style="float: left" class="cache">
                                                    <span class="nouveau"><?=NOUVEAU?></span>
                                                    <a id="partager_bibliotheque_lien" href="javascript:void(0)"><?=BIBLIOTHEQUE_PROPOSITION_PARTAGE?></a>
                                                </div>
                                                <div id="recherche_bibliotheque">
                                                    <?= RECHERCHER_BIBLIOTHEQUE ?><br/>
                                                    <input type="text" style="width:300px" name=""/>
                                                    <button style="width: 30px;">OK</button>
                                                </div>
                                            <?php
                                            } ?>
                                            <br/><br/>
                                            <div id="bibliotheque" style="width:100%;height:100%"></div>
                                        <?php
                                        }
                                        break;
                                    case 'options':
                                        require_once('Edge.class.php');
                                        if (isset($_POST['texture1'])) {
                                            for ($i = 1; $i <= 2; $i++) {
                                                $requete_update_texture = 'UPDATE users SET Bibliotheque_Texture' . $i . '=\'' . $_POST['texture' . $i] . '\' WHERE id=' . $id_user;
                                                DM_Core::$d->requete($requete_update_texture);
                                                $requete_update_sous_texture = 'UPDATE users SET Bibliotheque_Sous_Texture' . $i . '=\'' . $_POST['sous_texture' . $i] . '\' WHERE id=' . $id_user;
                                                DM_Core::$d->requete($requete_update_sous_texture);
                                            }
                                            $requete_suppr_ordres = 'DELETE FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur=' . $id_user;
                                            DM_Core::$d->requete($requete_suppr_ordres);
                                            foreach ($_POST as $index => $valeur) {
                                                if (strpos($index, 'magazine_') !== false) {
                                                    list($pays, $magazine) = explode('_', substr($index, strlen('magazine_')));
                                                    $requete_ajout_ordre = 'INSERT INTO bibliotheque_ordre_magazines(Pays,Magazine,Ordre,ID_Utilisateur) '
                                                        . 'VALUES (\'' . $pays . '\',\'' . $magazine . '\',' . $valeur . ',' . $id_user . ')';
                                                    DM_Core::$d->requete($requete_ajout_ordre);

                                                }
                                            }
                                        }
                                        ?>
                                        <form method="post" action="?action=bibliotheque&amp;onglet=options">
                                        <span style="text-decoration:underline"><?= TEXTURE ?> : </span><br/>
                                        <select style="width:300px;" id="texture1" name="texture1">
                                            <option id="chargement_sous_texture"><?= CHARGEMENT ?>...</option>
                                        </select>
                                        <br/><br/>
                                        <span style="text-decoration:underline"><?= SOUS_TEXTURE ?> : </span><br/>
                                        <select style="width:300px;" id="sous_texture1" name="sous_texture1">
                                            <option id="vide"><?= SELECTIONNER_TEXTURE ?></option>
                                        </select>
                                        <br/><br/><br/>
                                        <span style="text-decoration:underline"><?= TEXTURE_ETAGERE ?>
                                            : </span><br/>
                                        <select style="width:300px;" id="texture2" name="texture2">
                                            <option id="chargement_sous_texture"><?= CHARGEMENT ?>...</option>
                                        </select>
                                        <br/><br/>
                                        <span style="text-decoration:underline"><?= SOUS_TEXTURE_ETAGERE ?>
                                            : </span><br/>
                                        <select style="width:300px;" id="sous_texture2" name="sous_texture2">
                                            <option id="vide"><?= SELECTIONNER_TEXTURE ?></option>
                                        </select>
                                        <br/><br/><br/>
                                        <span style="text-decoration:underline"><?= ORDRE_MAGAZINES ?>
                                            : </span><br/>
                                        <?= EXPLICATION_ORDRE_MAGAZINES ?><br/><br/>
                                        <?php
                                        DM_Core::$d->maintenance_ordre_magazines($id_user);?>
                                        <div id="liste_magazines">
                                            <?php
                                            $requete_ordre_magazines = 'SELECT Pays, Magazine, Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur=' . $id_user . ' ORDER BY Ordre';
                                            $resultat_ordre_magazines = DM_Core::$d->requete_select($requete_ordre_magazines);
                                            $publication_codes = [];
                                            foreach ($resultat_ordre_magazines as $magazine) {
                                                $publication_codes[] = $magazine['Pays'] . '/' . $magazine['Magazine'];
                                            }
                                            list($noms_pays, $noms_magazines) = Inducks::get_noms_complets($publication_codes);
                                            foreach ($resultat_ordre_magazines as $magazine) {
                                                $num_ordre = $magazine['Ordre'];
                                                $nom_pays = $magazine['Pays'];
                                                $nom_magazine = $magazine['Magazine'];
                                                $pays_complet = $noms_pays[$nom_pays];
                                                $magazine_complet = $noms_magazines[$nom_pays . '/' . $nom_magazine];
                                                ?>
                                                <div style="margin-top:10px;height:40px;"
                                                     class="magazine_deplacable"
                                                     id="<?= $nom_pays ?>_<?= $nom_magazine ?>">
                                                    <div class="handle"
                                                         style="float:left;text-align:center;border:1px solid white;width:40px">
                                                        <img alt="<?= $nom_pays ?>"
                                                             src="images/flags/<?= $nom_pays ?>.png"/>
                                                        <br/><?= $nom_magazine ?>
                                                    </div>
                                                    <div style="float:left;margin-left: 5px;margin-top: 7px;">
                                                        <?= $magazine_complet ?> (<?= $pays_complet ?>)
                                                    </div>
                                                    <input type="hidden"
                                                           name="magazine_<?= $nom_pays ?>_<?= $nom_magazine ?>"
                                                           value="<?= $num_ordre ?>"/>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <br/>
                                        <input type="submit" class="valider" value="<?= VALIDER ?>"/>
                                        </form>
                                        <?php

                                        break;

                                    case 'participer':
                                        require_once('Edge.class.php');
                                        echo INTRO_PARTICIPER_BIBLIOTHEQUE_1;
                                        ?><br/><br/><?php
                                        $pourcentage_visible = Edge::getPourcentageVisible($id_user);
                                        if ($pourcentage_visible == 100) {
                                            echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_IMPOSSIBLE;
                                        } else {
                                            require_once 'captcha/securimage/securimage.php';

                                            $image = new Securimage();
                                            $captcha_correcte = $image->check($_POST['captcha_code']) === true;
                                            if ($captcha_correcte) {
                                                ?><?= MERCI_CONTRIBUTION ?><br/><?= EMAIL_ENVOYE; ?><?php
                                                mail('admin@ducksmanager.net', 'Proposition d\'aide de ' . $_SESSION['user'] . ' pour la bibliothèque',
                                                    $_POST['texte_participation'], 'From: ' . $_SESSION['user'] . '<' . $_POST['email'] . '>');
                                            } else {
                                                ?><span style="color: red"><?= ERREUR_CAPTCHA ?></span><br/><br/><?php
                                            }
                                            if (!isset($_POST['code']) || !$captcha_correcte) {
                                                echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_1
                                                    . '<span style="font-weight: bold">' . $pourcentage_visible . '</span>'
                                                    . INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_2;
                                                ?><br/><br/><?php
                                                echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_3;
                                                ?><br/><?php
                                                echo INTRO_PARTICIPER_BIBLIOTHEQUE_PARTICIPATION_DEMANDEE_4;
                                                ?>
                                                <br/><br/>
                                                <form
                                                    action="?session_id=<?= session_id() ?>&amp;action=bibliotheque&amp;onglet=participer"
                                                    method="post">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <?= VOTRE_ADRESSE_EMAIL ?> :
                                                            </td>
                                                            <td>
                                                                <input type="text" name="email"/>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?= SPECIFIER_NUMEROS_BIBLIOTHEQUE ?> :
                                                            </td>
                                                            <td>
                                                                <textarea cols="40" rows="10"
                                                                          name="texte_participation"></textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?= RECOPIER_CODE ?> :
                                                            </td>
                                                            <td>
                                                                <?php
                                                                require_once 'captcha/securimage/securimage.php';
                                                                echo Securimage::getCaptchaHtml(['input_text' => '']);
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td>
                                                                <input type="submit" class="valider"
                                                                       value="<?= VALIDER ?>"/>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </form>
                                            <?php
                                            }
                                        }
                                        break;

                                    case 'contributeurs':
                                        $requete_contributeurs = 'SELECT Nom, Texte FROM bibliotheque_contributeurs';
                                        $contributeurs = DM_Core::$d->requete_select($requete_contributeurs);
                                        ?>
                                        <div style="border:1px solid white">
                                            <h2 style="text-align:center"><?= INTRO_CONTRIBUTEURS_BIBLIOTHEQUE ?></h2>
                                            <?php
                                            foreach ($contributeurs as $contributeur) {
                                                ?>
                                                <span
                                                    style="font-size:18px;line-height:20px;"><?= $contributeur['Nom'] ?></span> <?= $contributeur['Texte'] ?>
                                                <br/>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                        break;
                                    }
                                ?></div><?php
                            break;

                            case 'gerer':
                                $resultat_tranches_collection_ajoutees = DM_Core::$d->get_tranches_collection_ajoutees($id_user, true);
                                $nb_nouvelles_tranches = count($resultat_tranches_collection_ajoutees);
                                if ($nb_nouvelles_tranches > 0) {
                                    ?><div class="confirmation">
                                    <?php
                                        echo $nb_nouvelles_tranches.' ';
                                        if ($nb_nouvelles_tranches === 1) {
                                            echo BIBLIOTHEQUE_NOUVELLE_TRANCHE;
                                        }
                                        else {
                                            echo BIBLIOTHEQUE_NOUVELLES_TRANCHES;
                                        }
                                    ?>
                                    </div><?php
                                }
                                $requete_maj_dernier_acces = 'UPDATE users SET DernierAcces=CURRENT_TIMESTAMP WHERE ID='.$id_user;
                                DM_Core::$d->requete($requete_maj_dernier_acces);

                                $l=DM_Core::$d->toList($id_user);
                                if (isset($_GET['supprimer_magazine'])) {
                                    list($pays,$magazine)=explode('.',$_GET['supprimer_magazine']);
                                    $l_magazine=$l->sous_liste($pays,$magazine);
                                    $l_magazine->remove_from_database ($id_user);
                                }
                                ?>
                                <h2><?=GESTION_COLLECTION?></h2><br />
                                <?php
                                $onglets= [
                                        GESTION_NUMEROS_COURT=> ['ajout_suppr',GESTION_NUMEROS],
                                        GESTION_COMPTE_COURT=> ['compte',GESTION_COMPTE]];
                                if (!isset($_GET['onglet']))
                                    $onglet='ajout_suppr';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet, $onglets, 'onglet', '?action=gerer');
                                switch($onglet) {
                                    case 'compte':
                                        if (isset($_POST['submit_options'])) {
                                        	if ($_SESSION['user'] == 'demo') {
                                        		echo OPERATION_IMPOSSIBLE_MODE_DEMO.'<br />';
                                        	}
                                        	else {
												$erreur=null;
												$requete_verif_mot_de_passe='SELECT Email FROM users WHERE ID='.$id_user.' AND password=sha1(\''.$_POST['ancien_mdp'].'\')';
												$mot_de_passe_ok = count(DM_Core::$d->requete_select($requete_verif_mot_de_passe)) > 0;
												if ($mot_de_passe_ok) {
													$mot_de_passe_nouveau = $_POST['nouveau_mdp'];
													$mot_de_passe_nouveau_confirm = $_POST['nouveau_mdp_confirm'];
													if (strlen($mot_de_passe_nouveau) < 6) {
														$erreur = MOT_DE_PASSE_6_CHAR_ERREUR;
													}
													elseif ($mot_de_passe_nouveau != $mot_de_passe_nouveau_confirm) {
														$erreur = MOTS_DE_PASSE_DIFFERENTS;
													}
													else {
														$requete_modif_mdp='UPDATE users SET password=sha1(\''.$mot_de_passe_nouveau.'\') WHERE ID='.$id_user;
														DM_Core::$d->requete($requete_modif_mdp);
														echo MOT_DE_PASSE_CHANGE;
													}
												}
												else {
													$erreur = MOT_DE_PASSE_ACTUEL_INCORRECT;
												}
												?><br /><br /><?php
												if (is_null($erreur)) {
		                                            echo MODIFICATIONS_OK.'<br />';
		                                            $est_partage=isset($_POST['partage']) && $_POST['partage']=='on'?'1':'0';
		                                            $est_video=isset($_POST['video']) && $_POST['video']=='on'?'1':'0';
		                                            DM_Core::$d->requete('UPDATE users SET AccepterPartage='.$est_partage.', AfficherVideo='.$est_video.', '
		                                                                .'Email=\''.$_POST['email'].'\' '
		                                                                .'WHERE ID='.$id_user);
		                                        }
		                                        else {
													?><span style="color:red"><?=$erreur?></span><?php
		                                    	}
                                        	}
                                        }
                                        $resultat_partage=DM_Core::$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        $resultat_email=DM_Core::$d->requete_select('SELECT Email FROM users WHERE ID='.$id_user);
                                        ?>
                                        <form action="?action=gerer&amp;onglet=compte" method="post">
                                        <br /><?=ADRESSE_EMAIL?> : <br />
                                        <input type="text" name="email" style="width: 200px" value="<?php
                                        if (!is_null($resultat_email[0]['Email'])) {
											echo $resultat_email[0]['Email'];
                                        }?>" /><br /><br /><br />
                                        <span style="text-align: center;text-decoration: underline">
                                        	<?=MOT_DE_PASSE_CHANGEMENT?>
                                        </span>
                                        <br /><?=MOT_DE_PASSE_ACTUEL?> : <br />
                                        <input type="password" name="ancien_mdp" style="width: 100px" value="" />
	                                    <br />
                                        <br /><?=MOT_DE_PASSE_NOUVEAU?> : <br />
                                        <input type="password" name="nouveau_mdp" style="width: 100px" value="" />
	                                    <br />
                                       	<br /><?=MOT_DE_PASSE_NOUVEAU_CONFIRMATION?> : <br />
                                        <input type="password" name="nouveau_mdp_confirm" style="width: 100px" value="" />
                                        <br /><br /><br />
                                        <input type="checkbox" name="partage"<?php
                                        if ($resultat_partage[0]['AccepterPartage']==1) {
											?>checked="checked"<?php 
										} ?>/><?=ACTIVER_PARTAGE?>
										<br />
                                        <br />
                                        <input type="checkbox" name="video"
                                        <?php
                                        if (DM_Core::$d->user_afficher_video()) {
                                            ?>checked="checked"<?php
                                        } ?> /><?=AFFICHER_VIDEO?>
                                        <br />
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
                                        
                                        if (Util::getBrowser() == 'Android') {
											encart_WhatTheDuck();
											?><br /><br /><?php 
										}

                                        if (isset($_GET['onglet_magazine']) && $_GET['onglet_magazine'] !== 'new') {
                                            list($onglets_pays,$onglets_magazines)=$l->liste_magazines($_GET['onglet_magazine'],true);
                                        }
                                        else {
											list($onglets_pays,$onglets_magazines)=$l->liste_magazines(null,true);
                                        }

                                        if (isset($_GET['onglet_magazine']) && $_GET['onglet_magazine'] === 'new' && !isset($_POST['magazine'])) {
                                            echo REMPLIR_INFOS_NOUVEAU_MAGAZINE;
                                            ?>
                        <br /><br />
                        <table style="border:0">
                            <tr>
                                <td style="width:400px">
                                    <form method="get" action="?">
                                        <input type="hidden" name="action" value="gerer" />
                                        <input type="hidden" name="onglet" value="ajout_suppr" />
                                        <input type="hidden" id="form_pays" value="" />
                                        <input type="hidden" id="form_magazine" value="" />
                                        <input type="hidden" id="onglet_magazine" name="onglet_magazine" value="" />
                                        <span style="text-decoration:underline"><?=PAYS_PUBLICATION?> : </span><br />
                                        <select style="width:300px;" onchange="select_magazine()" id="liste_pays">
                                            <option id="chargement_pays"><?=CHARGEMENT?>...
                                        </select><br /><br />
                                        <span style="text-decoration:underline"><?=PUBLICATION?> : </span><br />
                                        <select style="width:300px;" onchange="magazine_selected()" id="liste_magazines">
                                            <option id="vide"><?=SELECTIONNER_PAYS?>
                                        </select>
                                        <br /><br />
                                        <input id="validerAjoutMagazine" type="submit" class="valider" value="<?=VALIDER?>" />
                                    </form>
                                </td>
                                <td style="vertical-align:top">
                                    <br />
                                    <?=RECHERCHE_MAGAZINE_1?>
                                    <?=RECHERCHE_MAGAZINE_2?>
                                    
                                    <div id="recherche_bibliotheque" style="display:block;margin-top: 0;">
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
											if ($nb_numeros == 0) {
                                                if (!isset($_GET['onglet_magazine'])) {
                                                    ?><?= COLLECTION_VIDE_1 ?><br/>
                                                      <?= COLLECTION_VIDE_2 ?><br/><br/><?php
                                                }
											}
											else {
                                                ?><?= POSSESSION_MAGAZINES_1 ?> <?= $nb_numeros ?> <?= NUMEROS ?>.
                                                  <?= POSSESSION_MAGAZINES_2 ?> <?= $nb_magazines ?>
                                                  <?= POSSESSION_MAGAZINES_3 ?> <?= $nb_pays ?> <?= PAYS ?>.
                                                <br/>
                                                  <?= POSSESSION_MAGAZINES_4 ?><br/><br/>
                                                <br/>
                                            <?php
                                            }
                                            ?><?=RECHERCHE_MAGAZINE_1?>&nbsp;
                                            <b class="toggler_aide_recherche_magazine"><?=CLIQUEZ_ICI?></b>
                                            <b class="toggler_aide_recherche_magazine cache">^</b>
                                            <div id="aide_recherche_magazine" class="cache">
                                                <?=RECHERCHE_MAGAZINE_2?>
                                                <div id="recherche_bibliotheque" style="display:block;margin-top: 0;">
                                                    <input type="text" style="width:300px" name="" />
                                                    <button style="width: 30px;">OK</button>
                                                </div>
                                            </div>
                                            <?php

                                            Affichage::onglets_magazines($onglets_pays,$onglets_magazines);

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

                                                <br />

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
                                ?><?=IMPRESSION_COLLECTION_PRESENTATION_LISTES?>
                                <ul id="choix_impression">
                                	<li style="background-image: url('Listes/Classique_petit.png')">
                                		<?=CLASSIQUE_DESCRIPTION?>
                                		<br>
                                		<ul class="caract">
                                			<li>
                                				<?=CLASSIQUE_PLUS_1?>
                                			</li>
                                			<li>
                                				<?=CLASSIQUE_MOINS_1?>
                                			</li>
                                		</ul> 
                                		
                                		<br /><br /><a style="margin-top: 5px;" href="impression.php?type=classique" target="_blank"><?=IMPRESSION_COLLECTION_AVEC?><?=strtolower(CLASSIQUE_NOM)?></a><br>
                                	</li>
                                	<li style="background-image: url('Listes/CollecTable_petit.png')">
                                		<span class="exclusif"> <?=EXCLUSIF_DUCKSMANAGER?> </span><?=COLLECTABLE_DESCRIPTION?>
                                		<ul class="caract">
                                			<li>
                                				<?=COLLECTABLE_PLUS_1?>
                                			</li>
                                			<li>
                                				<a href="http://www.youtube.com/watch?v=PAg-g1cF148&hd=1" target="_blank"><?=CLIQUEZ_ICI?></a> <?=COLLECTABLE_DEMO?>
                                			</li>
                                		</ul> 
                                		<br /><br />
                                		<a href="impression.php?type=collectable" target="_blank"><?=IMPRESSION_COLLECTION_AVEC?><?=COLLECTABLE_NOM?></a>
                                	
                                	</li>
                                </ul>
                                
                                <?php 
                                break;

                            case 'agrandir':
                                $l=DM_Core::$d->toList($id_user);

                                $onglets= [ACHAT_VENTE_NUMEROS=> ['achat_vente',CONTACT_UTILISATEURS],
                                               AUTEURS_FAVORIS=> ['auteurs_favoris',AUTEURS_FAVORIS_TEXTE]];
                                if (!isset($_GET['onglet']))
                                    $onglet='achat_vente';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet, $onglets, 'onglet', '?action=agrandir');
                                switch($onglet) {
                                    case 'achat_vente':
                                        echo INTRO_ACHAT_VENTE;
                                        ?><br /><?php
                                        DM_Core::$d->liste_numeros_externes_dispos($id_user);
                                        break;
                                    case 'auteurs_favoris':
                                        $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat = \'0000-00-00\'';
                                        $resultat_auteurs_surveilles=DM_Core::$d->requete_select($requete_auteurs_surveilles);
                                        ?>
                                        <?=EXPLICATION_NOTATION_AUTEURS1?> <a target="_blank" href="?action=stats&onglet=auteurs"><?=EXPLICATION_NOTATION_AUTEURS2?></a>
                                        <?=EXPLICATION_NOTATION_AUTEURS3?>
                                        <br /><br />
                                        <?=SUGGESTIONS_ACHATS_QUOTIDIENNES?>
                                        <br /><br />
                                        <?php
                                        $auteur_note_existe=false;
                                        foreach($resultat_auteurs_surveilles as $auteur_surveille) {
                                            if ($auteur_surveille['Notation']!=-1) {
                                                $auteur_note_existe=true;
                                            }
                                        }
                                        if (count($resultat_auteurs_surveilles)>0) {
                                            if (!$auteur_note_existe) {
                                                echo AUTEURS_NON_NOTES;
                                            }
                                            else {

                                                ?><?=MONTRER_MAGAZINES_PAYS?>&nbsp;
                                                <select style="width:300px;" onchange="recharger_stats_auteurs()" id="liste_pays">
                                                    <option id="chargement_pays"><?=CHARGEMENT?>...
                                                </select>
                                                <div id="suggestions"><?php
                                                    include_once 'Stats.class.php';
                                                    $pays = (isset($_GET['pays']) && $_GET['pays'] !== 'ALL') ? $_GET['pays'] : null;
                                                    Stats::showSuggestedPublications($pays);
                                                ?></div><?php
                                            }
                                        }
                                        else {
                                            echo AUCUN_AUTEUR_SURVEILLE;
                                        }

                                        break;
                                }

                                break;
                                case 'bouquineries':
									?><h2><?=LISTE_BOUQUINERIES?></h2><?php
                                	echo INTRO_BOUQUINERIES;
                                	?><br /><br /><?php
                                	if (isset($_POST['ajouter'])) {
										$erreur=false;
										foreach (['nom','adresse_complete','coordX', 'coordY', 'commentaire'] as $champ) {
											$_POST[$champ]=mysqli_real_escape_string(Database::$handle, $_POST[$champ]);
											if (empty($_POST[$champ])) {
												$erreur=true;
												?><div style="color:red"><?=CHAMP_OBLIGATOIRE_1.ucfirst($champ).CHAMP_OBLIGATOIRE_2?></div><?php
											}
										}
										if (!$erreur) {
	                                		$requete='INSERT INTO bouquineries(Nom, AdresseComplete, Commentaire, ID_Utilisateur, CoordX, CoordY, Actif) VALUES (\''.$_POST['nom'].'\',\''.$_POST['adresse_complete'].'\',\''.$_POST['commentaire'].'\','.(is_null($id_user) ? 'NULL':$id_user).', '.$_POST['coordX'].', '.$_POST['coordY'].', 0)';
                                            DM_Core::$d->requete($requete);

                                            $entete = "MIME-Version: 1.0\r\n";
                                            $entete .= "Content-type: text/html; charset=iso-8859-1\r\n";
                                            $entete .= "To: admin@ducksmanager.net\r\n";
                                            $entete .= "From: admin@ducksmanager.net\r\n";
                                            mail('admin@ducksmanager.net','Ajout de bouquinerie','<a href="http://www.ducksmanager.net/backend/bouquineries.php">Validation</a>', $entete);
                                            ?>
                                            <span style="color: red">
                                                <?=EMAIL_ENVOYE.EMAIL_ENVOYE_BOUQUINERIE.MERCI_CONTRIBUTION?>
                                            </span>
                                            <br />
											<?php
										}
		                     		}
		                     ?>
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
							<form method="post" id="form_bouquinerie" action="?action=bouquineries">
								<table border="0">
									<tr>
										<td><label for="bouquinerie_nom"><?=NOM_BOUQUINERIE?> :</label></td>
										<td><input size="40" maxlength="25" id="bouquinerie_nom" name="nom" type="text" /></td>
									</tr>
									<tr>
										<td><label for="adresse_complete"><?=ADRESSE?> :</label></td>
										<td><input size="40" type="text" id="adresse_complete" name="adresse_complete"/></td>
									</tr>
									<tr>
										<td><label for="bouquinerie_commentaires"><?=COMMENTAIRES_BOUQUINERIE?></label><br />(<?=COMMENTAIRES_BOUQUINERIE_EXEMPLE?>)</td>
										<td><textarea id="bouquinerie_commentaires" name="commentaire" cols="41" rows="5"></textarea>
										</td>
									</tr>
									<tr>
										<td align="center" colspan="2">
                                            <br />
											<input name="ajouter" type="submit" value="<?=AJOUTER_BOUQUINERIE?>" />
										</td>
									</tr>
	                        	</table>
                                <input type="hidden" name="coordX" />
                                <input type="hidden" name="coordY" />
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
									<li><img src="images/demo_bibliotheque.jpg" alt="demo2_2"
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
									<br /> <img src="images/demo_bibliotheque2.jpg" alt="demo2_2" />
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
						<?php encart_WhatTheDuck();?>
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
    exit();
}

function formulaire_inscription() {
	$user= $_POST['user' ];
	$pass= $_POST['pass' ];
	$pass2=$_POST['pass2'];
	$email=$_POST['email'];
	$rawData=$_POST['rawData'];
	$erreur=null;
    if (isset($user)) {
		$erreur=Affichage::valider_formulaire_inscription($user, $pass, $pass2);
        if (!is_null($erreur)) {
            ?><span style="color:red"><?=$erreur?></span><?php
        }
    }
    if (!isset($user) || !is_null($erreur)) {
        ?>
        <form method="post" action="?action=new">
        <?php
        if (isset($rawData)) {
            ?><input type="hidden" name="rawData" value="<?=$rawData?>" /><?php
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
        DM_Core::$d->nouveau_user($user, $email, sha1($pass));
        if (isset($rawData)) {
            $l = new Liste($rawData);
            $l->add_to_database(DM_Core::$d->user_to_id($user));
        }
        creer_id_session($user, $pass);
    }
}

function creer_id_session($user,$pass) {
    $_SESSION['user']=$user;
    $_SESSION['pass']=sha1($pass);

    ?><script type="text/javascript">
        document.location.replace("?action=gerer");
    </script><?php
}

function encart_WhatTheDuck() {
?>
	<div style="width:300px;margin-top:20px;border:1px solid white">
		<a href="https://play.google.com/store/apps/details?id=net.ducksmanager.whattheduck"><img src="images/WhatTheDuck.png" style="float:left;margin-right:12px"/></a>
		<p style="margin-left:10px">
			<?=PUB_WHATTHEDUCK_1?>
			<a href="https://play.google.com/store/apps/details?id=net.ducksmanager.whattheduck"><b>What The Duck</b></a>
			<?=PUB_WHATTHEDUCK_2?>
			<br />
			<?=PUB_WHATTHEDUCK_3?>
		</p>
	</div><?php 
}
?>