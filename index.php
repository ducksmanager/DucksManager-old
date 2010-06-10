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
$types_listes=array();
$rep = "Listes/";
$dir = opendir($rep);
while (null != ($f = readdir($dir))) {
    if(is_file($rep.$f)) {
        if (startsWith($f,'Liste.') && endsWith($f,'.class.php')) {
            require_once($rep.$f);
            $nom_liste=str_replace('Liste.','',str_replace('.class.php','',$f));
            array_push($types_listes,$nom_liste);
        }
    }
}
require_once('Menu.class.php');
require_once('Affichage.class.php');
require_once('Inducks.class.php');

$menu=	array(L::_("collection")=>
        array("new"=>
                array("private"=>"never",
                        "link"=>true,
                        "text"=>L::_("nouvelle_collection")),
                "open"=>
                array("private"=>"never",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>L::_("ouvrir_collection")),
                "gerer"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>L::_("gerer_collection")),
                "stats"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>L::_("statistiques_collection")),
                "agrandir"=>
                array("private"=>"always",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>L::_("agrandir_collection")),
                "print"=>
                array("private"=>"always",
                        "link"=>true,
                        "text"=>L::_("imprimer_collection")),
                "logout"=>
                array("private"=>"always",
                        "link"=>true,
                        "text"=>L::_("deconnexion")))
        ,
        L::_("collection_inducks")=>
        array("import"=>
                array("private"=>"no",
                        "link"=>true,
                        "coa_related"=>true,
                        "text"=>L::_("importer_inducks"))/*,
				      "export"=>
					array("private"=>"no",
						  "link"=>false,
						  "text"=>L::_("exporter_inducks")*/
        )
        )
;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
    <head>
        <meta content="text/html; charset=ISO-8859-1"
              http-equiv="content-type">
        <title><?php echo L::_('titre');?></title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <!--[if IE]>
              <style type="text/css" media="all">@import "fix-ie.css";</style>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="boxes.css">
        <link rel="stylesheet" type="text/css" href="scriptaculous.css">
        <link rel="stylesheet" type="text/css" href="autocompleter.css">
        <link rel="stylesheet" type="text/css" href="csstabs.css">
        <link rel="stylesheet" href="protomenu.css" type="text/css" media="screen">
        <link rel="icon" type="image/png" href="favicon.png">
        <script type="text/javascript">
            var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
            document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
            try {
                var pageTracker = _gat._getTracker("UA-11433683-1");
                pageTracker._trackPageview();
            } catch(err) {}
        </script>
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
        new JS('js/sel_num.js');
        new JS('js/menu_contextuel.js');
        new JS('js/ajax.js');
        new JS('js/selection_menu.js');
        new JS('js/bouquineries.js');
        new JS('js/divers.js');

        if (isset($_GET['debug'])) {
            ?>
        <script type='text/javascript'
        src='http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js'></script>
            <?php }
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

    echo '<body id="body" style="margin:0" onload="';
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
        case 'gerer':
            echo 'defiler_log(\'DucksManager\');';
            if (isset($_GET['onglet_magazine'])) {
                echo 'init_observers_gerer_numeros();';

                if ($_GET['onglet_magazine']=='new') {
                    echo 'initPays();';
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
    echo '">';
    ?>

    <table
        style="text-align: left; color: white; background-color: rgb(61, 75, 95); width: 100%; height: 100%;border:0"
        cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td align="center" style="height:45px;padding-left:3px;background-color:rgb(61, 75, 95);width:160px;">
                    <table width="100%" style="width:100%">
                        <tr>
                            <td align="center" id="log" >&nbsp;</td>
                            <td align="center" id="loading" style="width:40px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="width:120px">
                                <div style="padding-left:5px;border:2px solid rgb(255, 98, 98);" id="connected">
                                    <?php
                                    echo '<img id="light" ';
                                    if (isset($_SESSION['user']) &&!($action=='logout'))
                                        echo 'src="vert.png" alt="O" />&nbsp;<span id="texte_connecte">'.L::_('connecte_en_tant_que').$_SESSION['user'].'</span>';
                                    else
                                        echo 'src="rouge.png" alt="X" />&nbsp;<span id="texte_connecte"><a href="?action=open">'.L::_('non_connecte').'</a></span><br /><br />';
                                    ?>
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
                            <p><a href="http://get.adobe.com/flashplayer"><?php echo L::_('telecharger_flash');?></a> <?php echo L::_('pour_voir_la_video');?>.</p>
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
                                    <b><a href="?"><?php echo L::_('accueil');?></a></b><br /><br />
                                    <?php
                                    foreach($menu as $item=>$infos) {
                                        echo '<span style="font-weight: bold; text-decoration: underline;">'.$item.'</span><br />';
                                        foreach($infos as $sous_item=>$infos_sous_item) {
                                            if ($infos_sous_item['private']=='no') {
                                                if (!$infos_sous_item['link']) {
                                                    echo ' <del>'.$infos_sous_item['text'].'</del><br />';
                                                    continue;
                                                }
                                                echo ' <a href="?action='.$sous_item.'">'.$infos_sous_item['text'].'</a><br>';
                                            }
                                            else {
                                                if (isset($_SESSION['user']) &&!($action=='logout')) {
                                                    if (!$infos_sous_item['link']) {
                                                        echo ' <del>'.$infos_sous_item['text'].'</del><br />';
                                                        continue;
                                                    }
                                                    if ($infos_sous_item['private']=='always'){
                                                        echo ' <a href="?action='.$sous_item.'">'.$infos_sous_item['text'].'</a><br>';
                                                    }
                                                }
                                                else if ($infos_sous_item['private']=='never'){
                                                    if (!$infos_sous_item['link']) {
                                                        echo ' <del>'.$infos_sous_item['text'].'</del><br />';
                                                        continue;
                                                    }
                                                    echo ' <a href="?action='.$sous_item.'">'.$infos_sous_item['text'].'</a><br>';
                                                }
                                            }
                                        }
                                        echo '<br />';
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
                    <?php if (!isset($_GET['action'])) echo '<h3>'.L::_('bienvenue').'</h3>';?>
                    <span id="contenu">


                        <?php
                        echo $texte_debut;
                        foreach($menu as $item=>$infos) {
                            foreach($infos as $sous_item=>$infos_sous_item) {
                                if ($sous_item==$action) {
                                    if (isset($infos_sous_item['coa_related'])) {
                                        require_once('Util.class.php');
                                        $contenu_page=Util::get_page('http://coa.inducks.org/maccount.php');
                                        if (!(strpos($contenu_page,'is experiencing technical difficulties') === false)) {
                                            echo '<span style="color:red;"><b>'.L::_('phrase_maintenance_inducks1').' <a href="coa.inducks.org">COA</a>, '.L::_('phrase_maintenance_inducks2').'<br />'
                                                    .L::_('phrase_maintenance_inducks3').'</span><br /><br />';
                                        }
                                    }
                                    if ($infos_sous_item['private']=='always' && !isset($_SESSION['user'])) {
                                        echo L::_('identification_obligatoire').'<br />';
                                        echo L::_('comment_s_identifier');
                                        $action='aucune';
                                    }
                                }
                            }
                        }
                        switch($action) {
                            case 'import':
                            /*if (isset($_SESSION['user'])) {
			echo L::_('import_impossible_si_connecte1').'<br />';
			echo L::_('import_impossible_si_connecte2');
			break;
		}*/
                                if (!isset($_POST['user'])) {
                                    afficher_form_inducks();
                                }
                                else echo L::_('importation_en_cours');
                                break;
                            case 'new':
                                echo '<table><tr><td colspan="2"></td></tr>';
                                echo '<tr><td><span id="user_text">'.L::_('nom_utilisateur').' : </span></td><td><input id="user" type="text">&nbsp;</td></tr>';
                                echo '<tr><td><span id="pass_text">'.L::_('mot_de_passe_6_char').' : </span></td><td><input id="pass" type="password">&nbsp;</td></tr>';
                                echo '<tr><td><span id="pass_text2">'.L::_('mot_de_passe_conf').' : </span></td><td><input id="pass2" type="password">&nbsp;</td></tr>';
                                echo '<tr><td colspan="2"><input type="submit" value="'.L::_('inscription').'" onclick="verif_valider_inscription($(\'user\'),$(\'pass\'),$(\'pass2\'),false)"></td></tr></table>';
                                break;
                            case 'open':
                                if (!isset($_SESSION['user'])) {
                                    echo L::_('identifiez_vous').'<br /><br />';
                                    echo '<form method="post" action="index.php?action=open">';
                                    echo '<table border="0"><tr><td>'.L::_('nom_utilisateur').' :</td><td><input type="text" name="user" /></td></tr>';
                                    echo '<tr><td>'.L::_('mot_de_passe').' :</td><td><input type="password" name="pass" /></td></tr>';
                                    echo '<tr><td align="center" colspan="2"><input type="submit" value="'.L::_('connexion').'"/></td></tr></table></form>';
                                }
                                break;
                            case 'logout':
                                session_destroy();
                                session_unset();
                                setCookie('user','',time()-3600);
                                setCookie('pass','',time()-3600);
                                echo L::_('deconnexion_ok');
                                break;
                            case 'gerer':
                                $d=new Database();
                                if (!$d) {
                                    echo L::_('probleme_bd');
                                    exit(-1);
                                }
                                $id_user=$d->user_to_id($_SESSION['user']);

                                echo '<span style="font-weight: bold; text-decoration: underline;">'.L::_('gestion_collection').'</span>';
                                $onglets=array(
                                        L::_('gestion_compte_court')=>array('compte',L::_('gestion_compte')),
                                        L::_('gestion_numeros_court')=>array('ajout_suppr',L::_('gestion_numeros')),
                                        L::_('gestion_acquisitions_court')=>array('acquisitions',L::_('gestion_acquisitions')),
                                        L::_('options')=>array('options',L::_('parametrage_compte')));
                                if (!isset($_GET['onglet']))
                                    $onglet='ajout_suppr';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet,$onglets,'onglet','?action=gerer',-1);
                                switch($onglet) {
                                    case 'compte':
                                        if (isset($_GET['vider']) || isset($_GET['supprimer'])) {
                                            if (isset($_GET['confirm']) && $_GET['confirm']=='true') {
                                                $action=isset($_GET['vider'])?'vider':'supprimer';
                                                switch ($action) {
                                                    case 'vider':
                                                        $requete='DELETE FROM numeros WHERE ID_Utilisateur='.$id_user;
                                                        $d->requete($requete);
                                                        echo L::_('numeros_supprimes').'.<br />';
                                                        break;
                                                    case 'supprimer':
                                                        $requete='DELETE FROM numeros WHERE ID_Utilisateur='.$id_user;
                                                        $d->requete($requete);
                                                        echo L::_('numeros_supprimes').'<br />';
                                                        $requete_compte='DELETE FROM users WHERE ID='.$id_user;
                                                        $d->requete($requete_compte);
                                                        session_destroy();
                                                        echo L::_('compte_supprime_deconnecte').'<br />';
                                                        break;
                                                }
                                            }
                                            else {
                                                echo L::_('operation_irreversible').'<br />'.L::_('continuer_oui_non').'<br />';
                                                $action=isset($_GET['vider'])?'vider':'supprimer';
                                                echo '<a href="?action=gerer&amp;onglet=compte&amp;'.$action.'=true&amp;confirm=true"><button>'.L::_('oui').'</button></a>&nbsp;';
                                                echo '<a href="?action=gerer"><button>'.L::_('non').'</button></a>';
                                            }
                                        }
                                        else {
                                            echo '<a href="?action=gerer&amp;onglet=compte&amp;vider=true">'.L::_('vider_liste').'</a><br /><br />'
                                                    .'<a href="?action=gerer&amp;onglet=compte&amp;supprimer=true">'.L::_('supprimer_compte').'</a><br />';
                                        }

                                        break;
                                    case 'ajout_suppr':
                                        $l=$d->toList($id_user);

                                        echo L::_('possession_magazines_1').'<br />'.L::_('possession_magazines_2').'<br />';
                                        //echo '<table border="0" width="20%">';
                                        $onglets_magazines=$l->liste_magazines();
                                        if (isset($_GET['onglet_magazine']))
                                            $onglet_magazine=$_GET['onglet_magazine'];
                                        $onglets_magazines[L::_('nouveau_magazine')]=array('new',L::_('ajouter_magazine'));
                                        //echo '<span id="onglets_magazines">';
                                        Affichage::onglets($onglet_magazine,$onglets_magazines,'onglet_magazine','?action=gerer&amp;onglet=ajout_suppr',3);

                                        if ($onglet_magazine=='new' && !isset($_POST['magazine'])) {
                                            echo L::_('remplir_infos_nouveau_magazine');
                                            ?>
                        <br /><br />
                        <form method="post" action="?action=gerer&amp;onglet=ajout_suppr&amp;onglet_magazine=new">
                            <input type="hidden" id="form_pays" name="pays" value="" />
                            <input type="hidden" id="form_magazine" name="magazine" value="" />
                            <input type="hidden" name="onglet_magazine" value="new" />
                            <span style="text-decoration:underline"><?php echo L::_('pays_publication');?> : </span><br />
                            <select style="width:300px;" onchange="select_magazine()" id="liste_pays">
                                <option id="chargement_pays"><?php echo L::_('chargement');?>...
                            </select><br /><br />
                            <span style="text-decoration:underline"><?php echo L::_('magazine');?> : </span><br />
                            <select style="width:300px;" onchange="magazine_selected()" id="liste_magazines">
                                <option id="vide"><?php echo L::_('selectionner_pays')?>
                            </select>
                            <br /><br />
                            <input type="submit" value="<?php echo L::_('valider');?>" />
                        </form><br /><br />
                        <span id="liste_numeros">
                        </span>
                                            <?php
                                        }
                                        else {
                                            echo '<table width="100%">';
                                            echo '<tr><td>';
                                            if (isset($_POST['magazine']))
                                                $onglet_magazine=$_POST['pays'].'/'.$_POST['magazine'];
                                            if (isset($onglet_magazine)) {
                                                list($pays,$magazine)=explode('/',$onglet_magazine);
                                                if (false!=($numeros=Inducks::get_numeros($pays,$magazine))) {
                                                    Affichage::afficher_etiquettes();
                                                    echo '<span id="liste_numeros">';
                                                    Affichage::afficher_numeros($l,$pays,$magazine,$numeros);
                                                    echo '</span>';
                                                    echo '</td><td>';
                                                }
                                                else echo L::_('erreur_recuperation_inducks');
                                            }
                                            echo '</td></tr></table>';
                                        }
                                        break;
                                    case 'acquisitions':
                                        $l=$d->toList($id_user);
                                        echo L::_('intro_acquisitions1').'<br />';
                                        echo L::_('intro_acquisitions2').'<br /><br />';
                                        echo '<table border="0" cellspacing="2px"><tr><td>';
                                        echo '<span id="liste_acquisitions">';
                                        Affichage::afficher_acquisitions(false);
                                        echo '</span></td>';
                                        echo '<td><span id="nouvelle_acquisition"></span>';
                                        echo '</td></tr></table>';
                                        break;
                                    case 'options':
                                        if (isset($_POST['submit_options'])) {
                                            echo L::_('modifications_ok').'<br />';
                                            if ($_POST['partage']=='on')
                                                $d->requete('UPDATE users SET AccepterPartage=1 WHERE ID='.$id_user);
                                            else
                                                $d->requete('UPDATE users SET AccepterPartage=0 WHERE ID='.$id_user);
                                        }
                                        $resultat_partage=$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        echo '<form action="?action=gerer&amp;onglet=options" method="post">';
                                        echo '<br /><input type="checkbox" name="partage"';
                                        if ($resultat_partage[0]['AccepterPartage']==1)
                                            echo ' checked="checked"';
                                        echo ' /> '.L::_('activer_partage').'<br />';
                                        echo '<input name="submit_options" type="submit" value="'.L::_('valider').'" /></form>';

                                        break;
                                }

                                break;
                            case 'stats':
                                $d=new Database();
                                if (!$d) {
                                    echo L::_('probleme_bd');
                                    exit(-1);
                                }
                                $id_user=$d->user_to_id($_SESSION['user']);
                                $l=$d->toList($id_user);
                                if (!isset($_GET['onglet']))
                                    $onglet='magazines';
                                else
                                    $onglet=$_GET['onglet'];
                                $l->statistiques($onglet);
                                break;

                            case 'print':
                                echo '<span style="font-weight: bold; text-decoration: underline;">'.L::_('impression_collection').' : </span><br /><br />';

                                echo L::_('intro_impression_collection1').'<br />';
                                echo L::_('intro_impression_collection2').'<br />';
                                echo '<br />';
                                echo '<div style="text-align:center;border:1px solid white;"><a target="_blank" href="print.php">'.L::_('clic_impression').'!</a></div><br /><br />';
                                echo L::_('intro_impression_collection3').'<br /><br />';
                                echo '<table border="1" cellpadding="4" cellspacing="2"><tr align="center"><td>'.L::_('affichage_liste').'</td><td>'.L::_('description').'</td></tr>';
                                $liste_exemple=new Liste();
                                $liste_exemple->ListeExemple();
                                foreach($types_listes as $type) {
                                    if ($type=='Series') continue;
                                    $objet =new $type();
                                    echo '<tr><td>';
                                    if ($type=='DMtable')
                                        echo '<iframe height="200px" width="400px" src="Liste.class.php?liste_exemple=true&amp;type_liste='.$type.'"></iframe>';
                                    else
                                        echo $objet->afficher($liste_exemple->collection);
                                    echo '</td><td>';
                                    echo $objet->description.'<br /><br />';
                                    echo '<img src="plus.png" /> : <br />';
                                    foreach($objet->les_plus as $plus) {
                                        echo '- '.$plus.'<br />';
                                    }
                                    echo '<br />';
                                    echo '<img src="moins.png" /> : <br />';
                                    foreach($objet->les_moins as $moins) {
                                        echo '- '.$moins.'<br />';
                                    }
                                    echo '</td></tr>';
                                }
                                echo '</table>';
                                break;

                            case 'agrandir':
                                $d=new Database();
                                if (!$d) {
                                    echo L::_('probleme_bd');
                                    exit(-1);
                                }
                                $id_user=$d->user_to_id($_SESSION['user']);
                                $l=$d->toList($id_user);

                                $onglets=array(L::_('achat_vente_numeros')=>array('achat_vente',L::_('contact_utilisateurs')),
                                               L::_('auteurs_favoris')=>array('auteurs_favoris',L::_('auteurs_favoris_texte')),
                                               L::_('completer_series')=>array('completer_series',L::_('completer_series_texte')),
                                               L::_('rechercher_bouquineries')=>array('bouquineries',L::_('rechercher_bouquineries_texte')));
                                if (!isset($_GET['onglet']))
                                    $onglet='achat_vente';
                                else
                                    $onglet=$_GET['onglet'];
                                Affichage::onglets($onglet,$onglets,'onglet','?action=agrandir',-1);
                                switch($onglet) {
                                    case 'achat_vente':
                                        echo L::_('intro_achat_vente').'<br />';
                                        $accepte=$d->requete_select('SELECT AccepterPartage FROM users WHERE ID='.$id_user);
                                        if ($accepte[0]['AccepterPartage']==0)
                                            echo L::_('comment_partager_collection').' <i><a href="?action=gerer&amp;onglet=options">'.L::_('page_options').'</a></i>.';
                                        $d->liste_numeros_externes_dispos($id_user);
                                        break;
                                    case 'auteurs_favoris':
                                        $onglets_auteurs=array(L::_('resultats_suggestions_mags')=>array('resultats',L::_('suggestions_achats')),
                                                L::_('preferences_auteurs')=>array('preferences',L::_('preferences_auteurs')));
                                        if (!isset($_GET['onglet_auteur']))
                                            $onglet_auteurs='resultats';
                                        else
                                            $onglet_auteurs=$_GET['onglet_auteur'];
                                        Affichage::onglets($onglet_auteurs,$onglets_auteurs,'onglet_auteur','?action=agrandir&amp;onglet=auteurs_favoris',-1);
                                        echo L::_('presentation_auteurs_favoris');
                                        switch ($onglet_auteurs) {
                                            case 'resultats':
                                                $d=new Database();
                                                $id_user=$d->user_to_id($_SESSION['user']);
                                                $requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
                                                $resultat_auteurs_surveilles=$d->requete_select($requete_auteurs_surveilles);

                                                echo '<br /><br />';
                                                echo L::_('suggestions_achats_quotidiennes').'<br />';
                                                $auteur_note_existe=false;
                                                foreach($resultat_auteurs_surveilles as $auteur_surveille) {
                                                    if ($auteur_surveille['Notation']!=-1) $auteur_note_existe=true;
                                                }
                                                if (count($resultat_auteurs_surveilles)>0) {
                                                    if (!$auteur_note_existe) echo L::_('auteurs_non_notes');
                                                    else {
                                                        echo L::_('lancer_calcul_suggestions_manuellement').'<br />';
                                                        ?><button onclick="stats_auteur(<?php echo $id_user;?>)"><?php echo L::_('lancer_calcul_suggestions');?></button>
                        <div id="resultat_stats"></div>
                                                        <?php }
                                                }
                                                else echo L::_('aucun_auteur_surveille');
                                                echo '<br /><br />';
                                                $d->liste_suggestions_magazines();
                                                break;
                                            case 'preferences':
                                                $d=new Database();
                                                $id_user=$d->user_to_id($_SESSION['user']);
                                                if (isset($_POST['auteur_nom'])) {
                                                    $d->ajouter_auteur($_POST['auteur_id'],$_POST['auteur_nom']);
                                                }
                                                echo '<br /><br />';
                                                echo L::_('auteurs_favoris_intro_1').'<br />'.L::_('statistiques_auteurs_intro_2');
                                                ?>
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
                                                    echo L::_('liste_auteurs_intro');
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
                                        echo L::_('intro_completer_series').'<br /><br />';
                                        break;
                                    case 'bouquineries':
                                        echo L::_('intro_bouquineries').'<br />';
                                        $d=new Database();
                                        if (!$d) {
                                            echo L::_('probleme_bd');
                                            exit(-1);
                                        }

                                        if (isset($_POST['ajouter'])) {
                                            $requete='INSERT INTO bouquineries(Nom, Adresse, CodePostal, Ville, Pays, Commentaire, ID_Utilisateur) VALUES (\''.$_POST['nom'].'\',\''.$_POST['adresse'].'\',\''.$_POST['cp'].'\',\''.$_POST['ville'].'\',\'France\',\''.$_POST['commentaire'].'\','.$id_user.')';
                                            echo '<span style="color:red">';
                                            if ($id_user==1)
                                                $d->requete($requete);
                                            else {
                                                mail('perel.bruno@wanadoo.fr','Ajout de bouquinerie',$requete);
                                                echo L::_('email_envoye');
                                            }
                                            echo L::_('merci_contribution').'</span><br />';
                                        }
                                        echo '<h2>'.L::_('liste_bouquineries').'</h2>';
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
                                                echo '<h3>'.$bouquinerie['Pays'].'</h3>';
                                            }
                                            if ($departement!=$departement_courant) {
                                                echo '<h4>'.L::_('departement').$departement_courant.'</h4>';
                                            }
                                            if ($ville!=$bouquinerie['Ville']) {
                                                echo '<h5>'.$bouquinerie['Ville'].'</h5>';
                                            }
                                            echo '<div style="cursor:help" title="'.$bouquinerie['Commentaire'].'">';
                                            echo '<b>'.$bouquinerie['Nom'].'</b>'.' : '
                                                    .$bouquinerie['Adresse'].','.$bouquinerie['CodePostal'].' '.$bouquinerie['Ville']
                                                    .' <i>('.L::_('propose_par').$bouquinerie['username'].')</i></div><br />';
                                            $pays=$bouquinerie['Pays'];
                                            $departement=$departement_courant;
                                            $ville=$bouquinerie['Ville'];
                                        }

                                        echo '<br /><br />';
                                        $id_user=$d->user_to_id($_SESSION['user']);

                                        echo '<h2>'.L::_('proposer_bouquinerie').'</h2>';

                                        echo L::_('presentation_bouquinerie1').'<br />';
                                        echo L::_('intro_nouvelle_bouquinerie').'<br />';
                                        echo L::_('prix_honnetes');
                                        echo '<br /><br />';
                                        echo '<form method="post" action="?action=agrandir&amp;onglet=bouquineries">';
                                        echo '<table border="0">';
                                        echo '<tr><td>'.L::_('nom_bouquinerie').' :</td><td><input maxlength="25" size="26" name="nom" type="text" /></td></tr>';
                                        echo '<tr><td>'.L::_('adresse').' :</td><td><textarea cols="20" name="adresse"></textarea></td></tr>';
                                        echo '<tr><td>'.L::_('code_postal').' :</td><td><input maxlength="11" name="cp" type="text" size="5" maxlength="5"/></td></tr>';
                                        echo '<tr><td>'.L::_('ville').' :</td><td><input maxlength="20" size="26" name="ville" type="text" /></td></tr>';
                                        echo '<tr><td>'.L::_('commentaires_bouquinerie').'<br />('.L::_('commentaires_bouquinerie_exemple').')</td><td><textarea name="commentaire" colspan="40" rowspan="5"></textarea>';
                                        //echo '<tr><td>Pays :</td><td><input name="pays" type="text" /></td></tr>';
                                        /*echo '<tr><td colspan="2"><div style="border:1px solid white;"><u>Exemples de prix : </u><br />';
				echo '<div id="liste_exemples"></div>';
				echo '<span id="ajouter_exemple"></span></div>';
				echo '<a href="javascript:void(0)" onclick="ajouter_exemple()">Ajouter un exemple de prix</a></td></tr>';*/
                                        echo '<tr><td align="center" colspan="2"><input name="ajouter" type="submit" value="'.L::_('ajouter_bouquinerie').'" /></td></tr>';
                                        echo '</table>';

                                        break;
                                }

                                break;

                            default:
                                echo '<br /><br />';
                                echo L::_('presentation1').'<br /><br />';
                                echo L::_('presentation2').'<br /><br /><br />';
                                echo '<table><tr>';
                                echo '<td width="500"><img src="images/demo4.png" /></td><td valign="center">'
                                        .'<b>'.L::_('presentation_gerer_titre').'</b><br /><br />'
                                        .L::_('presentation_gerer_1').'<br /><br />'
                                        .L::_('presentation_gerer_2').'<br /><br />'
                                        .L::_('presentation_gerer_3').'</td>';
                                echo '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td valign="center">'
                                        .'<b>'.L::_('presentation_stats_titre').'</b><br /><br />'
                                        .L::_('presentation_stats_1')
                                        .'<br /><br />'
                                        .L::_('presentation_stats_2').'<br /><br />'
                                        .L::_('presentation_stats_3');

                                echo '<br /><br /><br />';
                                echo '<span style="color:red">'.L::_('nouveau').'</span>'.L::_('annonce_agrandir_collection1');
                                echo '<br /><br /><br /><br />';
                                echo '<div style="border:1px solid white;text-align:center;">';
                                echo L::_('presentation_generale').'.<br />'
                                        .'<h3>'.L::_('bienvenue').'</h3>';
                                echo '</div>';
                                echo '</td>';
                                echo '<td><img width="350" src="images/demo123.png" /></td></tr>';
                                echo '</table>';

                                echo '<br />';
                                echo L::_('gratuit_aucune_limite').' <a href="?action=new">'.L::_('inscrivez_vous').'</a>';
                                break;
                        }
                        fin_de_page();

                        function afficher_form_inducks() {
                            echo L::_('entrez_identifiants_inducks').'.<br /><br />';
                            if (!isset($_SESSION['user']))
                                echo '<span style="color:red">'.L::_('attention_mot_de_passe_inducks').'</span><br />';
                            echo '<form method="post" action="index.php?action=import">';
                            echo '<table border="0"><tr><td>'.L::_('utilisateur_inducks').' :</td><td><input type="text" name="user" /></td></tr>';
                            echo '<tr><td>'.L::_('mot_de_passe_inducks').' :</td><td><input type="password" name="pass" /></td></tr>';
                            echo '<tr><td align="center" colspan="2"><input type="submit" value="'.L::_('connexion').'"/></td></tr></table></form>';
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
                    </span>
                </td>
                <td valign="top">
                    <script type="text/javascript"><!--
                        google_ad_client = "pub-0175030099331206";
                        /* 120x240, date de crï¿½ation 21/10/09 */
                        google_ad_slot = "2052175046";
                        google_ad_width = 120;
                        google_ad_height = 240;
                        //-->
                    </script>
                    <script type="text/javascript"
                            src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                    </script>
                </td>

            </tr>
            <tr style="height:3px;background-color:black;"><td colspan="5"></td></tr>
            <tr style="height:20px">
                <td align="center" style="padding-left:4px;width: 242px;">
                        <?php
                        $d=new Database();
                        if (!$d) {
                            echo L::_('probleme_bd');
                            exit(-1);
                        }
                        $resultat_cpt_users=$d->requete_select('SELECT count(username) as cpt_users FROM users');
                        echo $resultat_cpt_users[0]['cpt_users'].' '.L::_('utilisateurs_inscrits');
                        ?>
                </td>
                <td colspan="2" align="center">
                        <?php echo L::_('licence_inducks1');?> <a target="_blank" href="http://coa.inducks.org/inducks/COPYING"><?php echo L::_('licence_inducks2');?></a><br />
                        <?php echo L::_('licence_inducks3');?>
                </td>
                <td valign="bottom" align="right">
                        <?php
                        $rep = "locales/";
                        $dir = opendir($rep);
                        while ($f = readdir($dir)) {
                            if(is_file($rep.$f)) {
                                if (endsWith($f,'.php') && strpos($f,'lang')===false) {
                                    $nom_langue=substr($f,0,strrpos($f,'.'));
                                    echo '<a href="?'.str_replace('&','&amp;',$_SERVER['QUERY_STRING']).'&amp;lang='.$nom_langue.'">
                                          <img style="border:0" src="images/'.$nom_langue.'.jpg" alt="'.$nom_langue.'"/></a>';
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