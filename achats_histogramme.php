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

date_default_timezone_set('Europe/Paris');
global $noms_mois;
$noms_mois= ['Jan'=>'Jan','Feb'=>'Fev','Mar'=>'Mar','Apr'=>'Avr','May'=>'Mai','Juin'=>'Juin',
				 'Jul'=>'Juil','Aug'=>'Aou','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dec'];

$noms_mois_complets= ['Jan'=>'Janvier','Feb'=>'F�vrier','Mar'=>'Mars','Apr'=>'Avril','May'=>'Mai','Juin'=>'Juin',
						  'Jul'=>'Juillet','Aug'=>'Ao�t','Sep'=>'Septembre','Oct'=>'Octobre','Nov'=>'Novembre','Dec'=>'D�cembre'];

session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}

include_once ('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once ('Database.class.php');
Util::exit_if_not_logged_in();

$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$l=DM_Core::$d->toList($id_user);

$type=isset($_GET['type']) && $_GET['type'] == 'progressif' ? 'progressif' : 'normal'; 

$requete_liste_pays_magazines='SELECT DISTINCT CONCAT(Pays,\'/\',Magazine) AS publicationcode FROM numeros WHERE ID_Utilisateur='.$id_user;
$resultat_liste_pays_magazines=DM_Core::$d->requete_select($requete_liste_pays_magazines);
$couleurs= [];
$publication_codes= [];
foreach($resultat_liste_pays_magazines as $resultat) {
	$publicationcode=$resultat['publicationcode'];
	$publication_codes[]=$publicationcode;
	$couleurs[$publicationcode]='#'.random_hex_color();
}
list($noms_pays,$noms_magazines) = Inducks::get_noms_complets($publication_codes);

$requete_premier_mois='SELECT Date FROM achats WHERE ID_User='.$id_user.' AND Date > \'2000-01-01\' ORDER BY Date LIMIT 1';
$resultat_premier_mois=DM_Core::$d->requete_select($requete_premier_mois);
$mois_courant=substr($resultat_premier_mois[0]['Date'], 0,7);
$mois_affiche=date("M y", mktime(0, 0, 0, intval(substr($mois_courant, 5,2)), 1, substr($mois_courant, 0,4)));
$liste_mois= [$mois_courant];
$liste_mois_affiches= [$mois_affiche];
$liste_mois_affiches_complets= [$mois_affiche];
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
date_default_timezone_set('UTC');
$regex_date='#([^-]+)-([^-]+)-(.+)#is';
$titre = new title(utf8_encode(ACHATS));
$titre->set_style( "{font-size: 20px; color: #F24062; font-family:Tuffy; text-align: center;}" );

$requete_possessions_avant_achats='SELECT Count(Numero) AS cpt, Pays, Magazine '
								 .'FROM numeros n '
								 .'WHERE ID_Utilisateur='.$id_user.'  AND ID_Acquisition=-2 '
								 .'GROUP BY Pays,Magazine';
$resultats_possessions_avant_achats=DM_Core::$d->requete_select($requete_possessions_avant_achats);
$comptes_totaux_magazines= [];
foreach($resultats_possessions_avant_achats as $possession_avant_achats) {
	$pays=$possession_avant_achats['Pays'];
	$magazine=$possession_avant_achats['Magazine'];
	$nb=$possession_avant_achats['cpt'];
	$comptes_totaux_magazines[$pays.'/'.$magazine]=$nb;
}

$bar_stack = new bar_stack();
$bar_stack_pct = new bar_stack();
$max=0;
foreach ($liste_mois as $i=>$mois) {
	$requete='SELECT Count(a.ID_Acquisition) AS cpt, CONCAT(Pays,\'/\',Magazine) AS publicationcode '
			.'FROM numeros n '
			.'LEFT JOIN achats a ON a.ID_Acquisition = n.ID_Acquisition AND a.Date LIKE \''.$mois.'%\' '
			.'WHERE ID_Utilisateur='.$id_user.' '
			.'GROUP BY Pays,Magazine';
	$requete_resultat=DM_Core::$d->requete_select($requete);
	$sections= [];
	$cpt_mois=0;
	foreach($requete_resultat as $cpt) {
		$cpt_mois+=$cpt['cpt'];
	}
	$cpt_total=array_sum($comptes_totaux_magazines)+$cpt_mois;
	foreach($requete_resultat as $resultat) {
		$nb=intval($resultat['cpt']);
		if ($type == 'normal' && $nb == 0)
			continue;
		$publicationcode=$resultat['publicationcode'];
		$nom_complet_magazine=$noms_magazines[$publicationcode];//.($pays=='fr'?'':' ('.$noms_pays[$pays].')');
		if (!array_key_exists($publicationcode,$comptes_totaux_magazines))
			$comptes_totaux_magazines[$publicationcode]=0;
		$comptes_totaux_magazines[$publicationcode]+=$nb;
		if ($type == 'progressif') {
			$section = new bar_stack_value($comptes_totaux_magazines[$publicationcode],$couleurs[$publicationcode]);
			$section->set_tooltip(utf8_encode($liste_mois_affiches_complets[$i]).'<br>'
								 .$nom_complet_magazine.'<br>'
								 .'<i>'.MAGAZINE.'</i> : '.(($nb==0?'' : ('+'.$nb.', ')).TOTAL.' '.$comptes_totaux_magazines[$publicationcode]).'<br>'
								 .'<i>'.TOUS_MAGAZINES.'</i> : '.(($cpt_mois==0?'' : ('+'.$cpt_mois.', ')).TOTAL.' '.$cpt_total));
		}
		else {
			$section = new bar_stack_value($nb,$couleurs[$publicationcode]);
		
			$section->set_tooltip(utf8_encode($liste_mois_affiches_complets[$i]).'<br>'.$nom_complet_magazine
								  .'<br>'.utf8_encode($nb.' '.(($nb==0 || $nb==1) ? ACQUISITION : ACQUISITIONS).'<br>'.TOTAL.' : '.$cpt_mois));
		}
		$sections[]=$section;
	}
	if ($max < $cpt_mois)
		$max = $cpt_mois;
	$bar_stack->append_stack($sections);
}
$legendes= [];
ksort($couleurs);
foreach($couleurs as $publicationcode=>$couleur) {
    if (array_key_exists($publicationcode, $noms_magazines)) {
        $nom_complet_magazine = $noms_magazines[$publicationcode];
    }
    else { // Magazine ayant disparu d'Inducks
        $nom_complet_magazine = $publicationcode;
    }
	$legendes[]=new bar_stack_key( $couleur, $nom_complet_magazine, 13 );
}
$bar_stack->set_keys($legendes);

$y = new y_axis();
if ($type == 'progressif') {
	$max=$cpt_total;
}
$y->set_range( 0, $max, intval($max/10) );

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


$LARGEUR_MOYENNE_TITRE_MAGAZINE=250;
$NB_TITRES_MAGAZINE_PAR_LIGNE=3;
$HAUTEUR_1_NUMERO = $type == 'progressif' ? 2 : 6;
$HAUTEUR_LIGNE_TITRE_MAGAZINE=25;

$largeur_barres = count($liste_mois)<=5 ? 250 : (25*count($liste_mois) + 40);
$largeur_titres_magazines=$LARGEUR_MOYENNE_TITRE_MAGAZINE*$NB_TITRES_MAGAZINE_PAR_LIGNE;
$largeur = max($largeur_barres,$largeur_titres_magazines);

$hauteur_barres = max(400,$max * $HAUTEUR_1_NUMERO + 50);
$hauteur_titres_magazines = intval($HAUTEUR_LIGNE_TITRE_MAGAZINE*(count($resultat_liste_pays_magazines)/(($largeur/$LARGEUR_MOYENNE_TITRE_MAGAZINE)-0.5)));

$hauteur = $hauteur_barres + $hauteur_titres_magazines;

if ($largeur > $_GET['largeur_max'] -10)
	$largeur = $_GET['largeur_max'] -10;

if ($hauteur > $_GET['hauteur_max'] -10)
	$hauteur = $_GET['hauteur_max'] -10;

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
swfobject.embedSWF("open-flash-chart.swf", "my_chart", "<?php echo $largeur;?>", "<?=$hauteur?>", "9.0.0");
</script>

<script type="text/javascript">

function ofc_ready(){
	parent.$('iframe_graphique').writeAttribute({'width':'<?php echo 80+$largeur;?>px','height':'<?=(70+$hauteur)?>px'});

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

</script>


</head>
<body>

<div id="my_chart"></div>
<?php if (isset($_GET['debug'])) {
	echo ($largeur_barres.'+'.$largeur_titres_magazines).'x'.($hauteur_barres.'+'.$hauteur_titres_magazines);
}
?>
<br>
</body>
</html>