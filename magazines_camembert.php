<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once('Database.class.php');
require_once('Inducks.class.php');
$d=new Database();
@session_start();
if (!$d) {
	echo L::_('probleme_bd');
	exit(-1);
}
$id_user=$d->user_to_id($_SESSION['user']);
$l=$d->toList($id_user);
$counts=array();
$total=0;
foreach($l->collection as $pays=>$numeros_pays) {
	$counts[$pays]=array();
	foreach($numeros_pays as $magazine=>$numeros) {
		$counts[$pays][$magazine]=count($numeros);
		$total+=count($numeros);
	}
}
$cpt_magazines=array();
$autres=0;
$explode_pays=array();
$cpt_pays=0;
$valeurs_magazines=array();
$cles_magazines=array();
$nb_magazines_autres=0;
foreach($counts as $pays=>$magazines) {
	$liste_magazines=Inducks::get_noms_complets_magazines($pays);
	foreach($magazines as $magazine=>$cpt) {
		if ($cpt/$total<0.01) {
			$autres+=$cpt;
			$nb_magazines_autres++;
		}
		else {
			$valeur=new pie_value($cpt,$magazine);
			$valeur->set_tooltip($liste_magazines[$magazine]
								 .utf8_encode('<br>'.L::_('numeros_possedes').' : '.$cpt.' ('.intval(100*$cpt/$total).'%)'));
			array_push($valeurs_magazines,$valeur);
			//$cpt_magazines[$liste_magazines[$magazine].' ('.$cpt.')']=$cpt;
		}
	}
	$cpt_pays++;
}
if ($autres!=0) {
	$valeur_autres=new pie_value($autres,L::_('autres'));
	$valeur_autres->set_tooltip(utf8_encode(L::_('autres').' ('.$nb_magazines_autres.' '.L::_('magazines__lowercase').')'
						 		.'<br>'.L::_('numeros_possedes').' : '.$autres.' ('.intval(100*$autres/$total).'%)'));
	array_push($valeurs_magazines,$valeur_autres);
}
$title=new title(L::_('part_magazines_collection'));
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