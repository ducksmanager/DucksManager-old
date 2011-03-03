<?php
function random_hex_color(){
    return sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
}

function toNomMoisTraduit($str) {
    global $noms_mois;
    foreach($noms_mois as $nom_anglais=>$nom_francais) {
        $str=str_replace($nom_anglais,$nom_francais,$str);
    }
    return $str;
}

function toNomMoisTraduitComplet($str) {
    global $noms_mois_complets;
    foreach($noms_mois_complets as $nom_anglais=>$nom_francais) {
        $str=str_replace($nom_anglais,$nom_francais,$str);
    }
    return $str;
}

global $noms_mois;
$noms_mois=array('Jan'=>'Jan','Feb'=>'Fev','Mar'=>'Mar','Apr'=>'Avr','May'=>'Mai','Juin'=>'Juin',
                 'Jul'=>'Juil','Aug'=>'Aou','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dec');

$noms_mois_complets=array('Jan'=>'Janvier','Feb'=>'Février','Mar'=>'Mars','Apr'=>'Avril','May'=>'Mai','Juin'=>'Juin',
                          'Jul'=>'Juillet','Aug'=>'Août','Sep'=>'Septembre','Oct'=>'Octobre','Nov'=>'Novembre','Dec'=>'Décembre');

session_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once ('Database.class.php');
$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$l=DM_Core::$d->toList($id_user);

$requete_liste_pays_magazines='SELECT DISTINCT Pays, Magazine FROM numeros WHERE ID_Acquisition IN (SELECT ID_Acquisition FROM achats WHERE ID_User='.$id_user.')';
$resultat_liste_pays_magazines=DM_Core::$d->requete_select($requete_liste_pays_magazines);
$couleurs=array();
foreach($resultat_liste_pays_magazines as $resultat_pays_magazine) {
    $pays=$resultat_pays_magazine['Pays'];
    $magazine=$resultat_pays_magazine['Magazine'];
    $couleurs[$pays.'/'.$magazine]='#'.random_hex_color();
    $noms_complets[$pays.'/'.$magazine]=implode(' - ',DM_Core::$d->get_nom_complet_magazine($pays,$magazine,true));
}

$requete_premier_mois='SELECT Date FROM achats WHERE ID_User='.$id_user.' ORDER BY Date LIMIT 1';
$resultat_premier_mois=DM_Core::$d->requete_select($requete_premier_mois);
$mois_courant=substr($resultat_premier_mois[0]['Date'], 0,7);
$mois_affiche=date("M y", mktime(0, 0, 0, intval(substr($mois_courant, 5,2)), 1, substr($mois_courant, 0,4)));
$liste_mois=array($mois_courant);
$liste_mois_affiches=array($mois_affiche);
$liste_mois_affiches_complets=array($mois_affiche);
while($mois_courant != date('Y-m')) {
    $mois_courant=date("Y-m", mktime(0, 0, 0, intval(substr($mois_courant, 5,2))+1, 1, substr($mois_courant, 0,4)));
    $mois_affiche=date("M y", mktime(0, 0, 0, intval(substr($mois_courant, 5,2)), 1, substr($mois_courant, 0,4)));
    $mois_affiche_complet=date("M Y", mktime(0, 0, 0, intval(substr($mois_courant, 5,2)), 1, substr($mois_courant, 0,4)));
    $mois_affiche_complet=toNomMoisTraduitComplet($mois_affiche_complet);
    $mois_affiche=toNomMoisTraduit($mois_affiche);
    $liste_mois[]=$mois_courant;
    $liste_mois_affiches[]=$mois_affiche;
    $liste_mois_affiches_complets[]=$mois_affiche_complet;
}
if (count($liste_mois)<=5)
	$largeur=250;
else
	$largeur=25*count($liste_mois);
date_default_timezone_set('UTC');
$regex_date='#([^-]+)-([^-]+)-(.+)#is';
$titre = new title(utf8_encode(ACHATS));
$titre->set_style( "{font-size: 20px; color: #F24062; font-family:Tuffy; text-align: center;}" );

$bar_stack = new bar_stack();
$bar_stack_pct = new bar_stack();
$max=0;
foreach ($liste_mois as $i=>$mois) {
	$val_non_poss_fr=$non_poss_fr[$index];
	$val_non_poss_etr=$non_poss_etr[$index];
        $requete='SELECT Count(Numero) AS cpt, Pays, Magazine FROM numeros '
                .'WHERE ID_Acquisition IN (SELECT ID_Acquisition FROM achats WHERE Date LIKE \''.$mois.'%\' AND ID_User='.$id_user.') AND ID_Utilisateur='.$id_user.' '
                .'GROUP BY Pays,Magazine';
        $requete_resultat=DM_Core::$d->requete_select($requete);
        $sections=array();
        $cpt_mois=0;
        foreach($requete_resultat as $cpt) {
            $cpt_mois+=$cpt['cpt'];
        }
        foreach($requete_resultat as $resultat) {
            $section = new bar_stack_value(intval($resultat['cpt']),$couleurs[$resultat['Pays'].'/'.$resultat['Magazine']]);
            $section->set_tooltip(utf8_encode($liste_mois_affiches_complets[$i]).'<br>'.$noms_complets[$resultat['Pays'].'/'.$resultat['Magazine']]
                                  .'<br>'.utf8_encode($resultat['cpt'].' '.($resultat['cpt']==1 ? ACQUISITION : ACQUISITIONS).'<br>'.TOTAL.' : '.$cpt_mois));
            $sections[]=$section;
        }
        if ($max < $cpt_mois)
            $max = $cpt_mois;
	$bar_stack->append_stack($sections);
}


$y = new y_axis();
$y->set_range( 0, $max, intval($max/10) );

$y_pct = new y_axis();
$y_pct->set_range( 0, 100, 5 );

$x = new x_axis();
$x_labels = new x_axis_labels();
$x_labels->set_labels($liste_mois_affiches);
$x_labels->set_vertical();
$x->set_labels( $x_labels );

$tooltip = new tooltip();
$tooltip->set_hover();
$chart = new open_flash_chart();
$chart->set_title( $titre );
$chart->add_element( $bar_stack );
$chart->set_x_axis( $x );
$chart->add_y_axis( $y );
$chart->set_tooltip( $tooltip );

$chart_pct = new open_flash_chart();
$chart_pct->set_title( $title );
$chart_pct->add_element( $bar_stack_pct );
$chart_pct->set_x_axis( $x );
$chart_pct->add_y_axis( $y_pct );
$chart_pct->set_tooltip( $tooltip );

?>

<html>
<head>

<link rel="stylesheet" type="text/css" href="style.css">
<!--[if IE]>
	<style type="text/css" media="all">@import "fix-ie.css";</style>
<![endif]-->
<script type="text/javascript" src="js/json/json2.js"></script>
<script type="text/javascript" src="js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("open-flash-chart.swf", "my_chart", "<?php echo $largeur;?>", "380", "9.0.0");
</script>

<script type="text/javascript">

function ofc_ready(){
	parent.$('iframe_graphique').writeAttribute({'width':'<?php echo 80+$largeur;?>px','height':'450px'});

}

function open_flash_chart_data()
{
    return JSON.stringify(data_1);
}

function load_1()
{
  tmp = findSWF("my_chart");
  x = tmp.load( JSON.stringify(data_1) );
}

function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}

var data_1 = <?php echo $chart->toPrettyString(); ?>;
var data_2 = <?php echo $chart_pct->toPrettyString(); ?>;

</script>


</head>
<body>

<div id="my_chart"></div>
<br>
</body>
</html>