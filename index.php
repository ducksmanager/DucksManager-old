<?php
require_once 'Util.class.php';

if (!Util::isLocalHost() && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' || strpos($_SERVER['HTTP_HOST'],'www.')!==false)){
    $redirect = 'https://ducksmanager.net' . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
date_default_timezone_set('Europe/Paris');
require_once 'DucksManager_Core.class.php';
require_once 'Liste.class.php';
require_once 'Menu.class.php';
require_once 'Affichage.class.php';
require_once 'Inducks.class.php';
if (Util::isLocalHost() || isset($_GET['dbg'])) {
    error_reporting(E_ALL);
}
else  {
    error_reporting(E_STRICT | E_WARNING);
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    setcookie('user','',time()-3600, '', 'ducksmanager.net');
    setcookie('pass','',time()-3600, '', 'ducksmanager.net');
    setcookie('is_sha1','true',time()-3600, '', 'ducksmanager.net');
}
else {
    if (isset($_SESSION['user']) && isset($_SESSION['pass']) && !isset($_COOKIE['user']) ) {
        setcookie('user',$_SESSION['user'],time()+3600, '', 'ducksmanager.net');
        setcookie('pass',$_SESSION['pass'],time()+3600, '', 'ducksmanager.net');
        setcookie('is_sha1','true',time()+3600, '', 'ducksmanager.net');
        $_COOKIE['user'] = $_SESSION['user'];
        $_COOKIE['pass'] = $_SESSION['pass'];
        $_COOKIE['is_sha1'] = 'true';
    }
    if (isset($_COOKIE['user'], $_COOKIE['pass']) && !DM_Core::$d->user_connects($_COOKIE['user'], $_COOKIE['pass'])) {
        $_SESSION['user']=$_COOKIE['user'];

        setcookie('user', $_COOKIE['user'],time()+3600, '','ducksmanager.net'); // On met les cookies à jour à chaque rafraichissement
        setcookie('pass', $_COOKIE['pass'],time()+3600, '', 'ducksmanager.net');
        setcookie('is_sha1', 'true',time()+3600, '', 'ducksmanager.net');
    }
}

$locales = [];
foreach(array_keys(Lang::$codes_inducks) as $nom_langue) {
    if(is_file('locales/'.$nom_langue.'.php')) {
        $nouvelle_url = str_replace('&','&amp;',$_SERVER['QUERY_STRING']);
        $nouvelle_url = preg_replace('#\??(?:&amp;)?lang=[a-z]+#u','',$nouvelle_url);
        $nouvelle_url = '?'.(empty($nouvelle_url) ? '' : $nouvelle_url.'&amp;').'lang='.$nom_langue;

        $locales[$nom_langue] = $nouvelle_url;
    }
}

$action= $_GET['action'] ?? null;
if (defined('TITRE_PAGE_'.strtoupper($action))) {
    $titre = constant('TITRE_PAGE_' . strtoupper($action));
}
else {
    $titre = TITRE_PAGE_ACCUEIL;
}
$id_user= empty($_SESSION['id_user']) ? null : $_SESSION['id_user'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
    <meta content="initial-scale=1.0, width=device-width" name="viewport">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" CONTENT="no-store" />
    <meta http-equiv="Expires" content="0" />
    <meta name="keywords" content="collection,bandes dessinées,disney,bibliothèque,statistiques,revues,magazines,inducks,gestion,bouquineries,don rosa,barks,picsou,donald,mickey,comics,bookcase,issues" />
    <link rel="manifest" href="manifest.json">
    <title><?=$titre.' - DucksManager'?></title>
    <link rel="stylesheet" type="text/css" href="css/style.css?VERSION">
    <link rel="stylesheet" type="text/css" href="css/autocompleter.css?VERSION">
    <link rel="stylesheet" type="text/css" href="css/csstabs.css?VERSION">
    <link rel="stylesheet" type="text/css" href="css/bibliotheque.css?VERSION">
    <link rel="stylesheet" type="text/css" href="css/stats.css?VERSION">
    <link rel="stylesheet" type="text/css" href="css/menu.css?VERSION" />
    <?php
    foreach($locales as $nom_langue=>$nouvelle_url) {
        ?><link rel="alternate" hreflang="<?=$nom_langue?>" href="<?=$nouvelle_url?>" /><?php
    }
    ?>
    <link rel="icon" type="image/png" href="favicon.png">
    <?php
    if (Util::isLocalHost()) {
        ?><script src="//localhost:35729/livereload.js"></script><?php
    }
    else {?>
        <!-- Piwik -->
        <script type="text/javascript">
            var _paq = [];
            _paq.push(["setCustomVariable", 1, "Utilisateur", "<?=$_SESSION['user']?>", "visit"]);
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="https://piwik.ducksmanager.net/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', '1']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <!-- End Piwik Code -->
        <script src="https://browser.sentry-cdn.com/4.5.3/bundle.min.js" crossorigin="anonymous"></script>
        <script type="text/javascript">
            Sentry.init({ dsn: 'https://a225a6550b8c4c07914327618685a61c@sentry.io/1385898' });
        </script>
        <?php
    }?>
    <script type="text/javascript">
        var debug=<?=isset($_GET['debug']) ? 'true':'false'?>;
        var locale = '<?=$_SESSION['lang']?>';
    </script>

    <!-- Bootstrap -->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap_override.css?VERSION">
    <link rel="stylesheet" type="text/css" href="css/flip.css?VERSION">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/locales/bootstrap-datepicker.fr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/locales/bootstrap-datepicker.en-GB.min.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/platform/1.3.5/platform.min.js"></script>

    <script type="text/javascript" src="js/menu.js?VERSION"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
    <script type="text/javascript" src="js/l10n.js?VERSION"></script>
    <script type="text/javascript" src="js/ajax.js?VERSION"></script>
    <script type="text/javascript" src="js/edges.js?VERSION"></script>
    <script defer type="text/javascript" src="js/dropdowns.js?VERSION"></script>
    <script defer type="text/javascript" src="js/turn.min.js?VERSION"></script>
    <script defer type="text/javascript" src="js/flip.js?VERSION"></script><?php

    if (!is_null($action)) {
        ?><script type="text/javascript" src="js/sel_num.js?VERSION"></script><?php
        if (!isset($_GET['action'])) {
            $_GET['action'] = '';
        }
        switch($_GET['action']) {
        case 'gerer': ?>
            <script type="text/javascript" src="js/menu_contextuel.js?VERSION"></script>
            <link rel="stylesheet" type="text/css" href="css/menu_contextuel.css?VERSION" />
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.min.js"></script>
            <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css" /><?php
        break;
        case 'bouquineries': ?>
            <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.js'></script>
            <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.css' rel='stylesheet' />
            <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v3.1.4/mapbox-gl-geocoder.min.js'></script>
            <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v3.1.4/mapbox-gl-geocoder.css' type='text/css' />
            <link href='css/bouquineries.css' rel='stylesheet' /><?php
        break;
        case 'bibliotheque':
        if (isset($_GET['user'])) {
            $user_bibliotheque = $_GET['user'];
            $est_partage_bibliotheque = true;
        }
        else {
            $user_bibliotheque = -1;
            $est_partage_bibliotheque = false;
        }
        $onglet = isset($_GET['onglet']) && in_array($_GET['onglet'], ['affichage', 'options'])
            ? $_GET['onglet']
            : 'affichage';
        ?>
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-sortable/0.9.13/jquery-sortable-min.js"></script>
            <link rel="stylesheet" type="text/css" href="css/sortable.css" />
            <script type="text/javascript">
                var user_bibliotheque = <?=is_null($user_bibliotheque) ? -1 : "'".$user_bibliotheque."'"?>;
                var est_partage_bibliotheque = <?=$est_partage_bibliotheque ? 1 : 0?>;
                var onglet = '<?=$onglet?>';
            </script><?php
            break;
            case 'stats':
                if (!isset($_GET['onglet'])) {
                    $_GET['onglet']='magazines';
                }
                ?><script type="text/javascript" src="js/stats.js?VERSION"></script><?php

                if ($_GET['onglet'] === 'auteurs'){ ?>
                    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/star-rating-svg@3.5.0/src/css/star-rating-svg.min.css"/>
                    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/star-rating-svg@3.5.0/dist/jquery.star-rating-svg.min.js"></script><?php
                }
            break;
            case 'agrandir':
                ?><script type="text/javascript" src="js/stats.js?VERSION"></script><?php
        }
    } ?>
</head><?php
$texte_debut='';
if ($action==='demo') {
    $action='open';
    $_POST['user']='demo';
    $_POST['pass']='demodemo';
}
if ($action==='open'&& isset($_POST['user'])) {
    if (!DM_Core::$d->user_connects($_POST['user'],$_POST['pass'])) {
        $texte_debut .= 'Identifiants invalides!<br /><br />';
    }
    else {
        creer_id_session($_POST['user'],$_POST['pass']);
    }
}

?>
<body id="body" style="margin:0" onload="charger_dropdowns();charger_evenements();charger_menu();charger_contenu_android();<?php
switch($action) {
    case 'bibliotheque':
        switch ($onglet) {
            case 'affichage':
                ?>charger_bibliotheque();<?php
                break;
            case 'options':
                ?>initTextures();<?php
                break;
        }
        break;
    case 'gerer':
        if (isset($_SESSION['user'])) {
            if ((!isset($_GET['onglet']) || $_GET['onglet'] === 'ajout_suppr') && !isset($_GET['onglet_magazine'])) {
                $l=DM_Core::$d->toList($id_user);
                $_GET['onglet_magazine'] = $l->get_publication_la_plus_possedee() ?: null;
            }
            if (isset($_GET['onglet_magazine'])) {
                $onglet_magazine=$_GET['onglet_magazine'];
                if ($onglet_magazine==='new') {
                    ?>initPays(false,'fr');<?php
                }
                else {
                    [$pays,$magazine] =explode('/',$onglet_magazine);
                    $numero = $_GET['numero'] ?? null;
                    if (is_null($numero)) {
                        ?>afficher_numeros('<?=$pays?>','<?=$magazine?>');<?php
                    }
                    else {
                        ?>afficher_numeros('<?=$pays?>','<?=$magazine?>','<?=$numero?>');<?php
                    }
                }
            }
            ?>charger_recherche();<?php
        }
        break;
    case 'stats':
        if (isset($_SESSION['user'])) {
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
                    case 'possessions':
                        ?>afficher_histogramme_possessions();<?php
                        break;
                }
            }
        }
        break;
    case 'agrandir':?>
            initPays(true, '<?=empty($_GET['pays']) ? 'ALL' : $_GET['pays']?>');<?php
        break;
    case 'bouquineries':?>
            initBouquineries();<?php
        break;
}
?>">
<div id="menu">
    <div id="medailles_et_login"><?php
        if (isset($_SESSION['user']) && $action !== 'logout') {
            ?><div id="medailles"><?php
            $radius = 42;
            $circonference = M_PI * $radius * 2;

            DmClient::get_service_results_for_dm('POST', '/collection/lastvisit');
            $points=DM_Core::$d->get_points([$_SESSION['id_user']]);
            $niveaux = Affichage::get_medailles($points[$_SESSION['id_user']]);
            foreach($niveaux as $type=>$cpt_et_niveau) {
                $cpt=$cpt_et_niveau['Cpt'];
                $niveau=$cpt_et_niveau['Niveau'];
                if ($niveau === 3) {
                    $progres_niveau = 0;
                    $title = sprintf(
                        constant('DETAILS_MEDAILLE_'.strtoupper($type).'_MAX'),
                        $cpt
                    );
                }
                else {
                    $min_cpt_niveau = $niveau === 0 ? 0 : Affichage::$niveaux_medailles[$type][$niveau];
                    $min_cpt_niveau_suivant = Affichage::$niveaux_medailles[$type][$niveau+1];
                    $diff_niveau_suivant = $min_cpt_niveau_suivant-$cpt;
                    $title = sprintf(
                        constant('DETAILS_MEDAILLE_'.strtoupper($type)),
                        $cpt,
                        $diff_niveau_suivant,
                        constant('MEDAILLE_'.($niveau+1))
                    );
                } ?>
                <div class="overlay">
                    <div class="title" title="<?=$title?>"></div><?php
                    if ($niveau < 3) {
                        $progres_niveau = ($cpt-$min_cpt_niveau) / ($min_cpt_niveau_suivant-$min_cpt_niveau);
                        if ($progres_niveau === 0) {
                            $progres_niveau = .01;
                        }
                        switch($niveau) {
                            case 0: $couleur ='bronze'; break;
                            case 1: $couleur ='argent'; break;
                            case 2: $couleur ='or'; break;
                            default: $couleur = ''; break;
                        }
                        $pct = (1-$progres_niveau)*$circonference;
                        ?>
                        <svg width="100" height="100" viewport="0 0 0 0 " version="1.1" xmlns="http://www.w3.org/2000/svg">
                        <circle r="<?=$radius?>" cx="50" cy="50" fill="transparent" stroke-dasharray="<?=$circonference?>" stroke-dashoffset="0"></circle>
                        <circle transform="rotate(270,0,0)" class="bar <?=$couleur?>" r="<?=$radius?>" cx="-50" cy="50" fill="transparent" stroke-dasharray="<?=$circonference?>" style="stroke-dashoffset: <?=$pct?>px"></circle>
                        </svg><?php
                    }?>
                </div>
                <img class="medaille" src="images/medailles/<?=$type?>_<?=$niveau?>_<?=$_SESSION['lang']?>.png" /><?php
            }
            ?></div><?php
            Affichage::afficher_statut_connexion(true);
        } else {
            Affichage::afficher_statut_connexion(false);
        }?>
    </div>
    <i class="glyphicon glyphicon-menu-hamburger toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>

    <div id="recemment">
        <h4><?= NEWS_TITRE ?></h4>
        <div id="evenements"><?= CHARGEMENT ?></div>
    </div>

    <div class="menu-list"><?php
        Menu::$action=$action;
        Menu::afficherMenus($menus); ?>
    </div>
</div>
<div id="zone_logo1">
    <a href="<?= isset($_SESSION['user']) ? '/?action=gerer' : '/' ?>">
        <img src="/logo_petit.png" />
    </a>
</div>
<div id="zone_logo2">
    <?php if (isset($_SESSION['user']) && isset($_GET['action']) && $_GET['action'] === 'gerer') { ?>
        <div class="android-only android-banner cache">
        <h5 class="title">What The Duck</h5>
        <img class="logo_android" src="/images/WhatTheDuck.png" />
        <div class="text"><?= PRESENTATION_WHATTHEDUCK_LONGUE ?></div>
        <a class="store_link" href="https://play.google.com/store/apps/details?id=net.ducksmanager.whattheduck" target="_blank">
            <img src="/images/google-play-badge-<?=$_SESSION['lang']?>.png" />
        </a>
        </div><?php
    } ?><?php
    if (!isset($_GET['action'])) {?>
        <div class="welcome">
        <h2><?=BIENVENUE?></h2>
        <h5><?=ACCROCHE?></h5>
        </div><?php
    } ?>
    <div id="contenu">
        <?php
        echo $texte_debut;
        if (isset($_SESSION['user']) && $action !== 'logout' && !Inducks::connexion_ok()) {
            ?><div class="alert alert-danger"><?=COA_KO_1?><br /><?=COA_KO_2?></div><?php
            fin_de_page($locales);
        }
        foreach($menus as $menu) {
            if (isset($menu->items)) {
                foreach($menu->items as $item) {
                    if ($item->nom === $action && (
                            ($item->est_prive === 'always' && !isset($id_user))
                            || ($item->est_prive === 'always_except_user_provided' && !(isset($_GET['user']) || isset($id_user))))) {
                        ?><div class="alert alert-warning">
                        <?=IDENTIFICATION_OBLIGATOIRE?><br />
                        <?=COMMENT_S_IDENTIFIER?>
                        </div><?php
                        $action=null;
                    }
                }
            }
        }

        switch($action) {
            case 'new':
                formulaire_inscription();
                break;
            case 'open':
                if (!isset($_SESSION['user'])) { ?>
                    <h3><?= CONNEXION ?></h3>
                    <br />
                    <form method="post" action="?action=open" style="width: 250px">
                        <div class="form-group">
                            <label for="user"><?= NOM_UTILISATEUR ?></label>
                            <input class="form-control" id="user" name="user" type="text" placeholder="<?= NOM_UTILISATEUR ?>">
                        </div>
                        <div class="form-group">
                            <label for="pass"><?= MOT_DE_PASSE ?></label>
                            <input type="password" class="form-control" id="pass" name="pass" placeholder="<?= MOT_DE_PASSE ?>">
                        </div>
                        <button type="submit" class="btn btn-default">Login</button>
                    </form>
                    <br />
                    <a href="?action=mot_de_passe_oublie"><?= MOT_DE_PASSE_OUBLIE ?></a>
                    <?php
                }
                break;
            case 'mot_de_passe_oublie' :
                if (isset($_POST['champs_remplis'])) {
                    if (empty($_POST['email'])) {
                        ?><div class="alert alert-danger"><?=MOT_DE_PASSE_OUBLIE_ERREUR_VIDE?></div><?php
                    }
                    else {
                        $resultat_reset_password = DmClient::get_service_results_for_dm('POST', '/ducksmanager/resetpassword/init', ['email' => $_POST['email']]);
                        if (!is_null($resultat_reset_password)) {
                            ?><div class="alert alert-info"><?=MOT_DE_PASSE_OUBLIE_OK?></div><?php
                        }
                    }
                }
                ?><?=MOT_DE_PASSE_OUBLIE_EXPLICATION?><br /><br />
                <form class="row" method="post" action="?action=mot_de_passe_oublie">
                    <input type="hidden" name="champs_remplis" />
                    <div class="col-sm-6">
                        <input class="form-control" type="text" name="email" placeholder="<?=ADRESSE_EMAIL?>" value="" />
                    </div>
                    <div class="col-sm-4">
                        <input class="btn btn-default" type="submit" value="<?=ENVOYER?>" />
                    </div>
                </form>
                <?php
                break;
        case 'reset_password':
        if (isset($_GET['token'])) {
            $resultat_check_token= DmClient::get_service_results_for_dm('POST', '/ducksmanager/resetpassword/checktoken/' .$_GET['token']);
            if (!is_null($resultat_check_token)) {
                if (isset($_POST['nouveau_mdp'])) {
                    $mot_de_passe_nouveau = $_POST['nouveau_mdp'];
                    $mot_de_passe_nouveau_confirm = $_POST['nouveau_mdp_confirm'];
                    if (strlen($mot_de_passe_nouveau) < 6) {
                        $erreur = MOT_DE_PASSE_6_CHAR_ERREUR;
                    } elseif ($mot_de_passe_nouveau !== $mot_de_passe_nouveau_confirm) {
                        $erreur = MOTS_DE_PASSE_DIFFERENTS;
                    }
                }
                if (isset($erreur)) {
                    ?><div class="alert alert-danger"><?=$erreur?></div><?php
                }
                if (!isset($erreur) && isset($_POST['nouveau_mdp'])) {
                    $resultat_reset_password = DmClient::get_service_results_for_dm('POST', '/ducksmanager/resetpassword', ['token' => $_GET['token'], 'password' => $_POST['nouveau_mdp']]);
                    ?><div class="alert alert-success"><?=MOT_DE_PASSE_CHANGE?></div><?php
                }
                else {?>
                    <form method="post" action="">
                    <h6 style="text-decoration: underline">
                        <?= MOT_DE_PASSE_CHANGEMENT ?>
                    </h6>
                    <div class="form-group">
                        <label for="nouveau_mdp"><?= MOT_DE_PASSE_NOUVEAU ?> : </label><br/>
                        <input class="form-control" type="password" id="nouveau_mdp" name="nouveau_mdp" style="width: 100px" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="nouveau_mdp_confirm"><?= MOT_DE_PASSE_NOUVEAU_CONFIRMATION ?> : </label><br/>
                        <input class="form-control" type="password" id="nouveau_mdp_confirm" name="nouveau_mdp_confirm" style="width: 100px" value=""/>
                    </div>
                    <button type="submit" class="btn btn-default">OK</button>
                    </form><?php
                }
            }
            else { ?>
                <div class="alert alert-danger">
                <?=sprintf(MOT_DE_PASSE_OUBLIE_ERREUR_TOKEN,
                sprintf('<a href="?action=mot_de_passe_oublie">%s</a>', MOT_DE_PASSE_OUBLIE)
            )?>
                </div><?php
            }
        }
        else {
            ?><div class="alert alert-danger">
            <?=sprintf(MOT_DE_PASSE_OUBLIE_ERREUR_TOKEN,
            sprintf('<a href="?action=mot_de_passe_oublie">%s</a>', MOT_DE_PASSE_OUBLIE)
        )?>
        </div><?php
        }
        break;
        case 'logout':
            session_destroy();
            session_unset();
            echo DECONNEXION_OK;
            break;
            break;
        case 'inducks': ?>
            <h3 id="dm-loves-inducks">
                <div id="dm-logo-small">&nbsp;</div>
                <div id="loves">&nbsp;</div>
                <div id="inducks-logo">&nbsp;</div>
            </h3>
            <div class="jumbotron">
                <p>
                    <?=IMPORTER_INDUCKS_DESCRIPTION_1?>
                </p>
                <p>
                    <?=IMPORTER_INDUCKS_DESCRIPTION_2?><br />
                    <?=sprintf(IMPORTER_INDUCKS_DESCRIPTION_3, COLLECTION_INDUCKS)?>
                </p>
                <p><a class="btn btn-primary" href="/?action=new" role="button"><?=INSCRIPTION?></a></p>
                <p><a class="btn btn-primary" href="/?action=open" role="button"><?=CONNEXION?></a></p>
            </div>
        <?php

        break;
        case 'importer_inducks': ?>
        <h3><?= IMPORTER_INDUCKS ?></h3><?php
        Inducks::import();
        break;
        case 'bibliotheque': ?>
            <div class="book template">
                <div class="magazine-viewport">
                    <div class="container">
                        <div class="magazine">
                            <div ignore="1" class="next-button"></div>
                            <div ignore="1" class="previous-button"></div>
                        </div>
                    </div>
                    <div class="bottom">
                        <div id="slider-bar" class="turnjs-slider">
                            <div id="slider"></div>
                        </div>
                    </div>
                </div>
            </div>

        <div id="conteneur_bibliotheque">
            <h2 id="titre_bibliotheque"></h2><br /><br />
            <?php
            if (!$est_partage_bibliotheque) {
                $onglets = [
                    BIBLIOTHEQUE_COURT => ['affichage', BIBLIOTHEQUE],
                    BIBLIOTHEQUE_OPTIONS_COURT => ['options', BIBLIOTHEQUE_OPTIONS],
                    BIBLIOTHEQUE_CONTRIBUTEURS_COURT => ['contributeurs', BIBLIOTHEQUE_CONTRIBUTEURS]];
                if (!isset($_GET['onglet'])) {
                    $onglet = 'affichage';
                }
                else {
                    $onglet = $_GET['onglet'];
                }
                Affichage::onglets($onglet, $onglets, 'onglet', '?action=bibliotheque');
            }
            switch ($onglet) {
                case 'affichage':
                    if (!$est_partage_bibliotheque) {
                        $resultat_tranches_collection_ajoutees = DM_Core::$d->get_tranches_collection_ajoutees($id_user);
                        if (count($resultat_tranches_collection_ajoutees) > 0) {
                            $publication_codes = [];
                            foreach ($resultat_tranches_collection_ajoutees as $tranche) {
                                $publication_codes[] = $tranche['publicationcode'];
                            }
                            $publication_codes = array_unique($publication_codes);
                            $magazines_complets = Inducks::get_noms_complets_magazines($publication_codes);
                            ?>
                            <div>
                                <?= BIBLIOTHEQUE_NOUVELLES_TRANCHES_LISTE ?><br/>
                                <?php
                                foreach ($resultat_tranches_collection_ajoutees as $tranche) {
                                    [$pays, $magazine] = explode('/', $tranche['publicationcode']);
                                    echo Affichage::afficher_texte_numero($pays, $magazines_complets[$tranche['publicationcode']], $tranche['issuenumber'])
                                        . Affichage::afficher_temps_passe($tranche['DiffSecondes']) . '<br />';
                                }
                                ?>
                            </div><br/><br/><?php
                        }
                        ?><div class="alert alert-info">
                        <?=sprintf(EXPLICATION_ORDRE_MAGAZINES, '<a href="?action=bibliotheque&onglet=options">'.BIBLIOTHEQUE_OPTIONS_COURT.'</a>')?>
                        </div><?php

                        $accepter_partage = DM_Core::$d->requete('
                                  SELECT AccepterPartage
                                  FROM users WHERE ID=?'
                                , [$id_user])[0]['AccepterPartage'] === '1';
                        if ($accepter_partage) {
                            ?><div class="alert alert-info">
                            <?=sprintf(EXPLICATION_PARTAGE_BIBLIOTHEQUE_ACTIVEE, '<a href="?action=gerer&amp;onglet=compte">'.GESTION_COMPTE_COURT.'</a>')?>
                            </div>
                            <div id="partager_bibliotheque" class="cache">
                            <div class="btn btn-default btn-sm" id="partager_bibliotheque_lien">
                                <?=BIBLIOTHEQUE_PROPOSITION_PARTAGE?>
                            </div>
                            </div><?php
                        }
                        else {
                            ?><div class="alert alert-warning">
                            <?=sprintf(EXPLICATION_PARTAGE_BIBLIOTHEQUE_DESACTIVEE, '<a href="?action=gerer&amp;onglet=compte">'.GESTION_COMPTE_COURT.'</a>')?>
                            </div><?php
                        }
                    }?>
                    <br/>
                    <span id="pcent_visible"></span>
                    <span id="pourcentage_collection_visible"><?=POURCENTAGE_COLLECTION_VISIBLE?></span>
                    <span id="chargement_bibliotheque"><?=CHARGEMENT?></span>

                    <?php if (!$est_partage_bibliotheque) { ?>
                    <div id="proposition_photo" class="cache">
                        <div id="tranches_possibles">
                            <?php Affichage::afficher_proposition_photo_tranche(); ?>
                        </div>
                    </div>
                    <div id="recherche_histoire" class="invisible">
                        <?= RECHERCHER_BIBLIOTHEQUE ?><br/>
                        <input type="text" class="form-control"/>
                    </div>
                    <?php
                } ?>
                    <div id="bibliotheque"></div>
                    <?php
                    Affichage::afficher_texte_numero_template();
                    Affichage::afficher_infobulle_tranche_template();
                    break;
                case 'options':
                    require_once 'Edge.class.php';
                    if (isset($_POST['texture1'])) {
                        for ($i = 1; $i <= 2; $i++) {
                            $requete_update_sous_texture = "UPDATE users SET Bibliotheque_Sous_Texture$i = ? WHERE id = ?";
                            DM_Core::$d->requete($requete_update_sous_texture, [$_POST['sous_texture' . $i], $id_user]);
                        }
                        $publicationSorts = array_values(array_filter($_POST['publicationcodes']));
                        DmClient::get_service_results_for_dm('POST', '/collection/bookcase/sort', ['sorts' => $publicationSorts]);
                    }

                    function buildTextureSelect($id, $title) { ?>
                        <div class="form-group">
                        <label for="<?=$id?>"><?=$title?></label>
                        <input type="hidden" id="<?=$id?>" name="<?=$id?>" />
                        <div class="select_sous_texture btn-group" id="select_<?=$id?>">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="selected" id="selected_<?=$id?>"></span>&nbsp;<span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu"></ul>
                        </div>
                        </div><?php
                    }

                    function buildPublicationSorts() { ?>
                        <div class="sortable-wrapper form-group">
                        <a class="reset-sortable btn btn-default btn-sm"><?=ORDRE_MAGAZINES_REINITIALISER?></a>
                        <label for="ordre_magazines"><?=ORDRE_MAGAZINES?></label>
                        <ol id="ordre_magazines" class="sortable">
                            <li class="template">
                                <input type="hidden" name="publicationcodes[]" value=""/>
                                <img class="flag" />&nbsp;
                                <span>Publication name</span>
                            </li>
                        </ol>
                        </div><?php
                    }?>
                    <form method="post" action="?action=bibliotheque&amp;onglet=options">
                        <input type="hidden" id="texture1" name="texture1" value="bois" />
                        <input type="hidden" id="texture2" name="texture2" value="bois" />

                        <div id="message_options"><?=CHARGEMENT?></div>
                        <div class="hidden">
                            <?php buildTextureSelect('sous_texture1', SOUS_TEXTURE)?>
                            <?php buildTextureSelect('sous_texture2', SOUS_TEXTURE_ETAGERE)?>
                            <?php buildPublicationSorts()?>
                            <br/><br/>
                            <div>
                                <input type="submit" class="btn btn-default" value="<?= VALIDER ?>"/>
                            </div>
                        </div>
                    </form>
                    <?php

                    break;

                case 'contributeurs':
                    $requete_contributeurs_internes = "
                            SELECT distinct users.ID, users.username AS Nom, '' AS Texte from users
                            inner join users_contributions c on users.ID = c.ID_user
                            where c.contribution IN ('photographe', 'createur')";
                    $contributeurs_internes = DM_Core::$d->requete($requete_contributeurs_internes);

                    $ids_contributeurs_internes = array_map(function($contributeur) {
                        return $contributeur['ID'];
                    }, $contributeurs_internes);
                    usort($contributeurs_internes, function($a, $b) {
                        return strcmp(strtolower($a['Nom']), strtolower($b['Nom']));
                    });

                    $details_collections=DM_Core::$d->get_details_collections($ids_contributeurs_internes);

                    $requete_contributeurs_externes = 'SELECT Nom, Texte FROM bibliotheque_contributeurs';
                    $contributeurs_externes = DM_Core::$d->requete($requete_contributeurs_externes);
                    usort($contributeurs_externes, function($a, $b) {
                        return strcmp(strtolower($a['Nom']), strtolower($b['Nom']));
                    });

                    $contributeurs = array_merge($contributeurs_internes, $contributeurs_externes);
                    ?>
                    <div id="contributeurs">
                    <h2><?= INTRO_CONTRIBUTEURS_BIBLIOTHEQUE ?></h2>
                    <?php
                    foreach ($contributeurs as $contributeur) {
                        ?><div class="contributeur"><?php

                        if (isset($contributeur['ID'])) {
                            Affichage::afficher_texte_utilisateur($details_collections[$contributeur['ID']]);
                        }
                        else {
                            echo utf8_encode($contributeur['Nom']).' '.$contributeur['Texte'];
                        }
                        ?></div>
                        <?php
                    }
                    ?>
                    </div><?php
                    break;
            }
            ?></div><?php
        break;

        case 'gerer':
        $l = DM_Core::$d->toList($id_user);
        if (isset($_GET['supprimer_magazine'])) {
            [$pays, $magazine] = explode('.', $_GET['supprimer_magazine']);
            $l_magazine = $l->sous_liste($pays, $magazine);
            $l_magazine->remove_from_database($id_user);
        }
        ?>
            <h2><?= GESTION_COLLECTION ?></h2><br/>

            <?php
            $onglets = [
                GESTION_NUMEROS_COURT => ['ajout_suppr', GESTION_NUMEROS],
                GESTION_COMPTE_COURT => ['compte', GESTION_COMPTE]];
            if (!isset($_GET['onglet'])) {
                $onglet = 'ajout_suppr';
            }
            else {
                $onglet = $_GET['onglet'];
            }
            Affichage::onglets($onglet, $onglets, 'onglet', '?action=gerer');
            switch ($onglet) {
                case 'compte':
                    ?><br/><?php
                    if (isset($_POST['submit_options'])) {
                        if ($_SESSION['user'] === 'demo') {
                            echo OPERATION_IMPOSSIBLE_MODE_DEMO . '<br />';
                        } else {
                            $erreur = null;
                            if (!empty($_POST['ancien_mdp'])) {
                                $mot_de_passe_ok = count(DM_Core::$d->requete('
                                      SELECT Email
                                      FROM users
                                      WHERE ID=? AND password=sha1(?)'
                                        , [$id_user, $_POST['ancien_mdp']])) > 0;
                                if ($mot_de_passe_ok) {
                                    $mot_de_passe_nouveau = $_POST['nouveau_mdp'];
                                    $mot_de_passe_nouveau_confirm = $_POST['nouveau_mdp_confirm'];
                                    if (strlen($mot_de_passe_nouveau) < 6) {
                                        $erreur = MOT_DE_PASSE_6_CHAR_ERREUR;
                                    } elseif ($mot_de_passe_nouveau !== $mot_de_passe_nouveau_confirm) {
                                        $erreur = MOTS_DE_PASSE_DIFFERENTS;
                                    } else {
                                        DM_Core::$d->requete('
                                              UPDATE users
                                              SET password=sha1(?) WHERE ID=?'
                                            , [$mot_de_passe_nouveau, $id_user]);
                                        ?><div class="alert alert-success"><?=MOT_DE_PASSE_CHANGE?></div><?php
                                    }
                                } else {
                                    $erreur = MOT_DE_PASSE_ACTUEL_INCORRECT;
                                }
                            }
                            if (is_null($erreur)) {
                                ?><div class="alert alert-success"><?=MODIFICATIONS_OK?></div><?php
                                $est_partage = isset($_POST['partage']) && $_POST['partage'] === 'on' ? '1' : '0';
                                $est_video = isset($_POST['video']) && $_POST['video'] === 'on' ? '1' : '0';
                                DM_Core::$d->requete('
                                      UPDATE users
                                      SET AccepterPartage=?, AfficherVideo=?, Email=?
                                      WHERE ID=?', [$est_partage, $est_video, $_POST['email'], $id_user]);
                            } else {
                                ?><div class="alert alert-danger"><?=$erreur?></div><?php
                            }
                        }
                    }
                    $resultat_options = DM_Core::$d->requete('SELECT Email, AccepterPartage FROM users WHERE ID=?', [$id_user]); ?>
                    <form action="?action=gerer&amp;onglet=compte" method="post">
                        <div class="form-group">
                            <label for="email"><?= ADRESSE_EMAIL ?> : </label><br/>
                            <input class="form-control" type="text" id="email" name="email" style="width: 200px" value="<?=$resultat_options[0]['Email']?>"/><br/><br/>
                        </div>
                        <h6 style="text-decoration: underline">
                            <?= MOT_DE_PASSE_CHANGEMENT ?>
                        </h6>

                        <div class="form-group">
                            <label for="ancien_mdp"><?= MOT_DE_PASSE_ACTUEL ?> : </label><br/>
                            <input class="form-control" type="password" id="ancien_mdp" name="ancien_mdp" style="width: 100px" value=""/>
                        </div>
                        <div class="form-group">
                            <label for="nouveau_mdp"><?= MOT_DE_PASSE_NOUVEAU ?> : </label><br/>
                            <input class="form-control" type="password" id="nouveau_mdp" name="nouveau_mdp" style="width: 100px" value=""/>
                        </div>
                        <div class="form-group">
                            <label for="nouveau_mdp_confirm"><?= MOT_DE_PASSE_NOUVEAU_CONFIRMATION ?> : </label><br/>
                            <input class="form-control" type="password" id="nouveau_mdp_confirm" name="nouveau_mdp_confirm" style="width: 100px" value=""/>
                        </div>
                        <br/><br/>
                        <div class="checkbox">
                            <label for="partage">
                                <input type="checkbox" id="partage" name="partage" <?php
                                if ($resultat_options[0]['AccepterPartage'] === '1') {
                                ?>checked="checked"<?php
                                } ?>/><?= ACTIVER_PARTAGE ?>
                            </label>
                        </div>
                        <div class="checkbox">
                            <label for="video">
                                <input type="checkbox" id="video" name="video" <?php
                                if (DM_Core::$d->user_afficher_video()) {
                                ?>checked="checked"<?php
                                } ?> /><?= AFFICHER_VIDEO ?>
                            </label>

                        </div>
                        <input name="submit_options" class="btn btn-success" type="submit" value="<?= VALIDER ?>"/>
                    </form>
                    <br/><br/><br/>
                    <?php
                    if (isset($_GET['vider']) || isset($_GET['supprimer'])) {
                        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
                            if ($_SESSION['user'] !== 'demo') {
                                $action = isset($_GET['vider']) ? 'vider' : 'supprimer';
                                switch ($action) {
                                    case 'vider':
                                        $requete = 'DELETE FROM numeros WHERE ID_Utilisateur=' . $id_user;
                                        DM_Core::$d->requete($requete);
                                        echo NUMEROS_SUPPRIMES . '.<br />';
                                        break;
                                    case 'supprimer':
                                        $requete = 'DELETE FROM numeros WHERE ID_Utilisateur=' . $id_user;
                                        DM_Core::$d->requete($requete);
                                        echo NUMEROS_SUPPRIMES . '<br />';
                                        $requete_compte = 'DELETE FROM users WHERE ID=' . $id_user;
                                        DM_Core::$d->requete($requete_compte);
                                        session_destroy();
                                        echo COMPTE_SUPPRIME_DECONNECTE . '<br />';
                                        break;
                                }
                            }
                        } else {
                            ?><div class="alert alert-warning">
                            <?= OPERATION_IRREVERSIBLE ?><br/><?= CONTINUER_OUI_NON ?>
                            <a href="?action=gerer&amp;onglet=compte&amp;<?= isset($_GET['vider']) ? 'vider' : 'supprimer' ?>=true&amp;confirm=true">
                                <button><?= OUI ?></button>
                            </a>&nbsp;
                            <a href="?action=gerer">
                                <button><?= NON ?></button>
                            </a>
                            </div>
                            <?php
                        }
                    } else if ($_SESSION['user'] !== 'demo') {?>
                        <a href="?action=gerer&amp;onglet=compte&amp;vider=true"><?= VIDER_LISTE ?></a><br/><br/>
                        <a href="?action=gerer&amp;onglet=compte&amp;supprimer=true"><?= SUPPRIMER_COMPTE ?></a>
                        <br/><?php
                    }

                    break;
                case 'ajout_suppr':
                    if ($_SESSION['user'] === 'demo') {
                        require_once 'init_demo.php';
                        $nb_minutes_avant_reset = 60 - strftime('%M', time());
                        if ($nb_minutes_avant_reset === 0) {
                            $nb_minutes_avant_reset = 60;
                        }
                        ?>
                        <div id="presentation_demo">
                        <h2><?= PRESENTATION_DEMO_TITRE ?></h2>
                        <?= PRESENTATION_DEMO . $nb_minutes_avant_reset . ' ' . MINUTES ?>
                        </div><?php
                    }

                    if (isset($_GET['onglet_magazine']) && $_GET['onglet_magazine'] !== 'new') {
                        [$onglets_pays, $onglets_magazines] = $l->liste_magazines($_GET['onglet_magazine']);
                    } else {
                        [$onglets_pays, $onglets_magazines] = $l->liste_magazines();
                    }

                    if (isset($_GET['onglet_magazine']) && $_GET['onglet_magazine'] === 'new' && !isset($_POST['magazine'])) {
                        echo REMPLIR_INFOS_NOUVEAU_MAGAZINE;
                        ?>
                        <br/><br/>
                        <form method="get" action="?">
                            <input type="hidden" name="action" value="gerer"/>
                            <input type="hidden" name="onglet" value="ajout_suppr"/>
                            <input type="hidden" id="form_pays" value=""/>
                            <input type="hidden" id="form_magazine" value=""/>
                            <input type="hidden" id="onglet_magazine" name="onglet_magazine" value=""/>
                            <div class="form-group">
                                <label for="liste_pays"><?=PAYS_PUBLICATION?></label>
                                <select class="form-control" style="width:300px;" onchange="select_magazine()" id="liste_pays">
                                    <option id="chargement_pays"><?= CHARGEMENT ?>
                                </select>
                            </div><br/>
                            <div class="form-group">
                                <label for="liste_magazines"><?= PUBLICATION ?></label>
                                <select class="form-control" style="width:300px;" onchange="magazine_selected()" id="liste_magazines">
                                    <option id="vide"><?= SELECTIONNER_PAYS ?>
                                </select>
                            </div>
                            <br/>
                            <input id="validerAjoutMagazine" type="submit" class="btn btn-default"
                                   value="<?= OK ?>"/>
                        </form>
                        <br/>
                        <br/>
                        <?= RECHERCHER_INTRO ?><br/>
                        <div id="recherche_histoire">
                            <br>
                            <?= RECHERCHER_GENERAL ?><br/>
                            <input type="text" class="form-control"/>
                        </div>
                        <br/><br/>
                        <?php
                    } else {
                        $l = DM_Core::$d->toList($id_user);
                        $nb_numeros = 0;
                        $nb_magazines = $nb_pays = 0;
                        foreach ($l->collection as $pays => $numeros_pays) {
                            $nb_pays++;
                            foreach (array_keys($numeros_pays) as $magazine) {
                                $nb_magazines++;
                                $nb_numeros += count($numeros_pays[$magazine]);
                            }
                        }
                        if ($nb_numeros === 0) {
                            if (!isset($_GET['onglet_magazine'])) {
                                ?><?= COLLECTION_VIDE_1 ?><br/>
                                <?= COLLECTION_CLIQUER_NOUVEAU_MAGAZINE ?><br/><br/><?php
                            }
                        } else {
                            include_once 'Stats.class.php';
                            Stats::showSuggestedPublications('ALL', true);
                            Affichage::afficher_dernieres_tranches_publiees();
                            ?><?= POSSESSION_MAGAZINES_INTRO ?>
                            <?php Affichage::afficher_stats_collection_court($nb_pays, $nb_magazines, $nb_numeros); ?>
                            <br/><?= CLIQUEZ_SUR_MAGAZINE_POUR_EDITER ?><br/><br/><?php
                        } ?>
                        <div id="recherche_histoire">
                            <?= RECHERCHER_GENERAL ?><br/>
                            <input type="text" class="form-control"/>
                        </div>
                        <?php

                        Affichage::onglets_magazines($onglets_pays, $onglets_magazines);

                        if (isset($onglet_magazine, $pays)) {
                            ?><?php if (isset($_GET['afficher_video']) && $_GET['afficher_video'] === 0) {
                                $requete_cacher_video = 'UPDATE users SET AfficherVideo=0 WHERE ID=' . $id_user;
                                DM_Core::$d->requete($requete_cacher_video);
                            }
                            ?><br/>

                            <div class="alert alert-info">
                                <?= INFO_AJOUT_NUMEROS_1 ?>
                                <span class="desktop-only"><?= INFO_AJOUT_NUMEROS_2_DESKTOP ?></span>
                                <span class="mobile-only"><?= INFO_AJOUT_NUMEROS_2_MOBILE ?></span>
                            </div>
                            <table width="100%">
                            <tr>
                                <td>
                                    <span id="liste_numeros" class="possedes manquants"><?= CHARGEMENT ?></span>
                                </td>
                                <td>
                                </td>
                            </tr>
                            </table><?php
                        }
                    }
                    break;
            }

            break;
        case 'stats':
            ?><h2><?=STATISTIQUES_COLLECTION?></h2><br /><?php
        $l=DM_Core::$d->toList($id_user);
        if (!isset($_GET['onglet'])) {
            $onglet = 'magazines';
        }
        else {
            $onglet = $_GET['onglet'];
        }
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

                $onglets= [
                    SUGGESTIONS_ACHATS=> ['suggestions_achat',AUTEURS_FAVORIS_TEXTE]
                ];

                $onglet=$_GET['onglet'] ?? 'suggestions_achat';
                Affichage::onglets($onglet, $onglets, 'onglet', '?action=agrandir');
                switch($onglet) {
                    case 'suggestions_achat':
                        $requete_auteurs_surveilles="SELECT Notation FROM auteurs_pseudos WHERE ID_User=$id_user";
                        $resultat_auteurs_surveilles=DM_Core::$d->requete($requete_auteurs_surveilles);
                        ?><div class="alert alert-info">
                        <?=EXPLICATION_NOTATION_AUTEURS1?>
                        <a href="?action=stats&onglet=auteurs"><?=EXPLICATION_NOTATION_AUTEURS2?></a>
                        <?=EXPLICATION_NOTATION_AUTEURS3?>
                        <br /><br />
                        <?=SUGGESTIONS_ACHATS_QUOTIDIENNES?>
                        </div>
                        <?php
                        $auteur_note_existe=false;
                        foreach($resultat_auteurs_surveilles as $auteur_surveille) {
                            if ($auteur_surveille['Notation']!==-1) {
                                $auteur_note_existe=true;
                            }
                        }
                        if (count($resultat_auteurs_surveilles)>0) {
                            if (!$auteur_note_existe) {
                                ?><div class="alert alert-warning">
                                <?=AUTEURS_NON_NOTES?>
                                </div><?php
                            }
                            else {
                                ?><?=MONTRER_MAGAZINES_PAYS?>&nbsp;
                                <select style="width:300px;" onchange="recharger_stats_auteurs()" id="liste_pays">
                                    <option id="chargement_pays"><?=CHARGEMENT?>
                                </select>
                                <?php
                                include_once 'Stats.class.php';
                                $pays = (isset($_GET['pays']) && $_GET['pays'] !== 'ALL') ? $_GET['pays'] : null;
                                Stats::showSuggestedPublications($pays);
                            }
                        }
                        else {
                            ?><div class="alert alert-warning">
                            <?=AUCUN_AUTEUR_NOTE_1?>
                            <?=sprintf(AUCUN_AUTEUR_NOTE_2_REDIRECTION, '<a href="?action=stats&onglet=auteurs">'.AUTEURS_COURT.'</a>')?>
                            <?=AUCUN_AUTEUR_NOTE_3?>
                            </div><?php
                        }

                        break;
                }

                break;
        case 'bouquineries':
            ?><h2><?=LISTE_BOUQUINERIES?></h2><?php
            echo INTRO_BOUQUINERIES;
            ?><br /><br /><?php
        if (isset($_POST['ajouter'])) {
        $erreur = false;
        foreach (['nom', 'adresse_complete', 'coordX', 'coordY', 'commentaire'] as $champ) {
        if (empty($_POST[$champ])) {
            $erreur = true;?>
            <div class="alert alert-danger">
                <?= CHAMP_OBLIGATOIRE_1 . ucfirst($champ) . CHAMP_OBLIGATOIRE_2 ?>
            </div><?php
            }
        }
        if (!$erreur) {
            $requete = '
                        INSERT INTO bouquineries(Nom, AdresseComplete, Commentaire, ID_Utilisateur, CoordX, CoordY, Actif)
                        VALUES (:nom, :adresse_complete, :commentaire, :id_user, :coordX, :coordY, 0)';
            DM_Core::$d->requete($requete, [
                ':nom' => $_POST['nom'],
                ':adresse_complete' => $_POST['adresse_complete'],
                ':commentaire' => $_POST['commentaire'],
                ':id_user' => is_null($id_user) ? null : $id_user,
                ':coordX' => $_POST['coordX'],
                ':coordY' => $_POST['coordY']
            ]);

            DmClient::get_service_results_for_dm(
                'POST', "/ducksmanager/email/bookstore-suggestion", is_null($id_user) ? [] : ['userid' => $id_user]
            );
            ?>
            <div class="alert alert-info">
                <?= EMAIL_ENVOYE . EMAIL_ENVOYE_BOUQUINERIE . MERCI_CONTRIBUTION ?>
            </div>
        <br/><?php
        }
        }?>
            <div id="map"></div>
            <div class="template infoWindow">
                <div id="siteNotice">
                </div>
                <h1 id="firstHeading" class="firstHeading Nom"></h1>
                <div id="bodyContent">
                    <p class="Commentaire"></p>
                    <p>Adresse : </p>
                    <p class="Adresse"></p><br />
                    <p class="Signature"></p>
                </div>
            </div>
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
                        <td><input class="form-control text_input" maxlength="25" id="bouquinerie_nom" name="nom" type="text" /></td>
                    </tr>
                    <tr>
                        <td><label for="adresse_complete"><?=ADRESSE?> :</label></td>
                        <td class="adresse_complete_wrapper"></td>
                    </tr>
                    <tr>
                        <td><label for="bouquinerie_commentaires"><?=COMMENTAIRES_BOUQUINERIE?></label><br />(<?=COMMENTAIRES_BOUQUINERIE_EXEMPLE?>)</td>
                        <td><textarea id="form-control bouquinerie_commentaires" class="form-control" name="commentaire" cols="41" rows="5"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">
                            <br />
                            <input class="btn btn-default" name="ajouter" type="submit" value="<?=AJOUTER_BOUQUINERIE?>" />
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="coordX" />
                <input type="hidden" name="coordY" />
            </form>
            <script type="text/javascript" src="js/bouquineries.js?VERSION"></script>
        <?php
        break;

        default:?>
            <div class="showcase center-block">
                <div class="row">
                    <div class="col-md-offset-1 col-lg-5">
                        <img src="images/montage DucksManager.jpg" alt="logo">
                    </div>
                    <div class="col-lg-5">
                        <div><?=PRESENTATION1?></div>
                        <div><?=PRESENTATION2?></div>
                        <div><?=GRATUIT_AUCUNE_LIMITE?></div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-offset-1 col-lg-5">
                        <h3><?=PRESENTATION_GERER_TITRE?></h3>
                        <div><?=PRESENTATION_GERER_1?></div>
                        <div><?=PRESENTATION_GERER_2?></div>
                        <div><?=PRESENTATION_GERER_3?></div>
                    </div>
                    <div class="col-lg-5">
                        <img src="images/demo2_2.png" alt="demo2_2">
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-offset-1 col-lg-5">
                        <video src="./demos/bookcase_demo.mp4" autoplay muted loop></video>
                    </div>
                    <div class="col-lg-5">
                        <h3><?=PRESENTATION_BIBLIOTHEQUE_TITRE?></h3>
                        <div><?=PRESENTATION_BIBLIOTHEQUE_1?></div>
                        <div><?=PRESENTATION_BIBLIOTHEQUE_2?></div>
                        <div><?=PRESENTATION_BIBLIOTHEQUE_3?></div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-offset-1 col-lg-5">
                        <h3><?=PRESENTATION_STATS_TITRE?></h3>
                        <div><?=PRESENTATION_STATS_1?></div>
                        <div><?=PRESENTATION_STATS_2?></div>
                        <div><?=PRESENTATION_STATS_3?></div>
                    </div>
                    <div class="col-lg-5">
                        <img src="images/demo3.png" alt="demo3">
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-offset-1 col-lg-5">
                        <video src="./demos/whattheduck_demo.mp4" autoplay muted loop></video>
                    </div>
                    <div class="col-lg-5">
                        <h3><?=PRESENTATION_WHATTHEDUCK_TITRE?></h3>
                        <div><?=PRESENTATION_WHATTHEDUCK_1?><a href="https://play.google.com/store/apps/details?id=net.ducksmanager.whattheduck"><b>What The Duck</b></a><?=PRESENTATION_WHATTHEDUCK_2?>
                        </div>
                        <?=PRESENTATION_WHATTHEDUCK_3?>
                    </div>
                </div>
                <div style="margin-right: 6px; text-align: center;">
                    <h3>
                        <a class="noborder btn btn-lg btn-success" href="?action=new"><?=INSCRIVEZ_VOUS?> </a>
                    </h3>
                </div>
            </div>
        <br /><?php
            break;
        }
        fin_de_page($locales);

        function fin_de_page($locales) {
        ?>
    </div>
</div>
<div id="footer">
    <div id="nb_users"><?php
        $resultat_cpt_users=DM_Core::$d->requete('SELECT count(username) as cpt_users FROM users');
        echo $resultat_cpt_users[0]['cpt_users'].' '.UTILISATEURS_INSCRITS; ?>
    </div>
    <div>
        <?=REMERCIEMENT_LOGO?>
        <br /><br />
        <?=LICENCE_INDUCKS1?>
        <a target="_blank" href="http://coa.inducks.org/inducks/COPYING"><?=LICENCE_INDUCKS2?></a>
        <br />
        <?=LICENCE_INDUCKS3?>
    </div>
    <div id="flags"><?php
        foreach($locales as $nom_langue=>$nouvelle_url) {
            ?>
            <a class="drapeau_langue" href="<?=$nouvelle_url?>">
                <img style="border:0" src="images/<?=$nom_langue?>.jpg" alt="<?=$nom_langue?>"/>
            </a>
            <?php
        } ?>
    </div>
</div>
</body>
</html><?php
}

function formulaire_inscription() {
    $user= $_POST['user' ] ?? '';
    $pass= $_POST['pass' ] ?? null;
    $pass2=$_POST['pass2'] ?? null;
    $email=$_POST['email'] ?? '';
    $erreur=null;
    if (isset($_POST['user' ])) {
        $erreur=Affichage::valider_formulaire_inscription($user, $pass, $pass2);
        if (!is_null($erreur)) {
            ?><div class="alert alert-danger"><?=$erreur?></div><?php
        }
    }
    if (!isset($_POST['user' ]) || !is_null($erreur)) {
        ?>
        <form method="post" action="?action=new">
            <table border="0">
                <tr><td><?=NOM_UTILISATEUR?> : </td><td><input required class="form-control" name="user" type="text" value="<?=$user?>" /></td></tr>
                <tr><td><?=ADRESSE_EMAIL?> : </td><td><input required class="form-control" name="email" type="text" value="<?=$email?>" /></td></tr>
                <tr><td><?=MOT_DE_PASSE_6_CHAR?> :</td><td><input required class="form-control" name="pass" type="password" /></td></tr>
                <tr><td><?=MOT_DE_PASSE_CONF?> :</td><td><input required class="form-control" name="pass2" type="password" /></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td colspan="2" style="text-align: center"><input class="btn btn-success" type="submit" value="<?=INSCRIPTION?>" /></td></tr>
            </table>
        </form>
        <?php
    }
    else {
        if (DM_Core::$d->nouveau_user($user, $email, sha1($pass))) {
            creer_id_session($user, $pass);
        }
    }
}

function creer_id_session($user,$pass) {
    $_SESSION['user']=$user;
    $_SESSION['pass']=sha1($pass);
    $_SESSION['id_user']=DM_Core::$d->user_to_id($_SESSION['user']);

    ?><script type="text/javascript">
        document.location.replace("?action=gerer");
    </script><?php
}
?>
