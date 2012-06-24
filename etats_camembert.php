<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once('Database.class.php');
require_once('Inducks.class.php');
Util::exit_if_not_logged_in();

$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$resultat=DM_Core::$d->requete_select('SELECT Count(Numero) AS c FROM numeros WHERE ID_Utilisateur='.$id_user);
$total=$resultat[0]['c'];
$cpt_etats=array();
$autres=0;
$valeurs_magazines=array();
$cles_magazines=array();
foreach(Database::$etats as $etat_court=>$infos_etat) {
	$resultat=DM_Core::$d->requete_select('SELECT Count(Numero) AS c FROM numeros WHERE ID_Utilisateur='.$id_user.' AND Etat LIKE \''.$etat_court.'\'');
	$cpt=$resultat[0]['c'];
	if ($cpt==0) continue;
	if ($cpt/$total<0.01) {
		$autres+=$cpt;
	}
	else {
		$valeur=new pie_value($cpt*100,utf8_encode(Database::$etats[$etat_court][0]));
		$valeur->set_tooltip(utf8_encode(Database::$etats[$etat_court][0].'<br>'.NUMEROS_POSSEDES).' : '.$cpt.' ('.round(100*$cpt/$total).'%)');
		array_push($valeurs_magazines,$valeur);
	}
}
if ($autres!=0) {
	$valeur_autres=new pie_value($autres*100,utf8_encode(AUTRES));
	$valeur_autres->set_tooltip(utf8_encode(AUTRES
				   .'<br>'.NUMEROS_POSSEDES).' : '.$autres.' ('.round(100*$autres/$total).'%)');
	array_push($valeurs_magazines,$valeur_autres);
}

$title=new title(utf8_encode(ETATS_MAGAZINES));
$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
$pie->add_animation( new pie_fade() );
$pie->set_values($valeurs_magazines);

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $pie );


$chart->x_axis=null;
?>

<html>
<head>

<script type="text/javascript" src="js/json/json2.js"></script>
<script type="text/javascript" src="js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("open-flash-chart.swf", "my_chart", "700", "380", "9.0.0");
</script>

<script type="text/javascript">

function ofc_ready(){
	parent.$('iframe_graphique').writeAttribute({'width':'750px','height':'450px'});
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
<br>
</body>
</html>