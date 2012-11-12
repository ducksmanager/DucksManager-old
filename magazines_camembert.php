<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once('Database.class.php');
require_once('Inducks.class.php');
Util::exit_if_not_logged_in();

@session_start();
$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$l=DM_Core::$d->toList($id_user);
$counts=array();
$total=0;
$requete_cpt_numeros_groupes='SELECT Pays,Magazine,Count(Numero) AS cpt '
							.'FROM numeros '
							.'WHERE ID_Utilisateur='.$id_user.' '
							.'GROUP BY Pays,Magazine '
							.'ORDER BY cpt';
$resultat_cpt_numeros_groupes=DM_Core::$d->requete_select($requete_cpt_numeros_groupes);

$publication_codes=array();
foreach($resultat_cpt_numeros_groupes as $resultat) {
	$publicationcode=$resultat['Pays'].'/'.$resultat['Magazine'];
	$cpt=intval($resultat['cpt']);
	$counts[$publicationcode]=$cpt;
	$total+=$cpt;
	$publication_codes[]=$publicationcode;
}
list($noms_pays,$noms_magazines)=Inducks::get_noms_complets($publication_codes);

$autres=0;
$explode_pays=array();
$valeurs_magazines=array();
$cles_magazines=array();
$nb_magazines_autres=0;
foreach($counts as $publicationcode=>$cpt) {
	$nom_complet_magazine=$noms_magazines[$publicationcode];
	if ($cpt/$total<0.01) {
		$autres+=$cpt;
		$nb_magazines_autres++;
	}
	else {
		list($pays,$magazine)=explode('/',$publicationcode);
		$valeur=new pie_value($cpt,$nom_complet_magazine);
		$valeur->set_tooltip($nom_complet_magazine
                            .utf8_encode('<br>'.NUMEROS_POSSEDES).' : '.$cpt.' ('.intval(100*$cpt/$total).'%)');
		array_push($valeurs_magazines,$valeur);
	}
}
if ($autres!=0) {
	$valeur_autres=new pie_value($autres,AUTRES);
	$valeur_autres->set_colour('#84359');
	$valeur_autres->set_tooltip(utf8_encode(AUTRES.' ('.$nb_magazines_autres.' '.MAGAZINES__LOWERCASE.')'
                               .'<br>'.NUMEROS_POSSEDES.' : '.$autres.' ('.intval(100*$autres/$total).'%)'));
	array_push($valeurs_magazines,$valeur_autres);
}
$title=new title(PART_MAGAZINES_COLLECTION);
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