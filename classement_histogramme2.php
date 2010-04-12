<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once('Database.class.php');
require_once('Inducks.class.php');


$d=new Database();
if (!$d) {
	echo L::_('probleme_bd');
	exit(-1);
}
/*$_SESSION['user']='nonoox';
$_SESSION['lang']='fr';*/
$id_user=$d->user_to_id($_SESSION['user']);
$url='http://coa.inducks.org/legend-country.php?xch=1&lg='.$codes_inducks[$_SESSION['lang']];
$handle = @fopen($url, "r");
if ($handle) {
	$buffer="";
	while (!feof($handle)) {
		$buffer.= fgets($handle, 4096);
	}
	fclose($handle);
}
else {
	echo L::_('erreur_connexion_inducks');
	exit(0);
}
$regex_pays='#<a href=country\.php\?c=([^>]+)>([^<]+)</a>#i';
$liste_pays=array();
preg_match_all($regex_pays,$buffer,$liste_pays);
foreach ($liste_pays[0] as $pays) {
	$liste_pays[preg_replace($regex_pays,'$1',$pays)]=utf8_decode(preg_replace($regex_pays,'$2',$pays));
}
$possede=array();
$total=array();
$possede_pct=array();
$total_pct=array();
$noms_magazines=array();
$noms_magazines_courts=array();

$l=$d->toList($id_user);
$counts=array();
$GLOBALS['firephp']->log($l->collection, 'Iterators');
foreach($l->collection as $pays=>$numeros_pays) {
	$counts[$pays]=array();
	foreach($numeros_pays as $magazine=>$numeros) {
		$counts[$pays][$magazine]=count($numeros);
	}
}
$j=0;
foreach($counts as $pays=>$magazines) {
	$liste_magazines=Inducks::get_noms_complets_magazines($pays);
	$adresse_publications_pays='http://coa.inducks.org/country.php?xch=1&lg=4&c='.$pays	;
	$handle = @fopen($adresse_publications_pays, "r");
	if ($handle) {
		$buffer="";
		while (!feof($handle)) {
			$buffer.= fgets($handle, 4096);
		}
		fclose($handle);
	}
	else {
		echo L::_('erreur_connexion_inducks');
	}
	foreach($magazines as $magazine=>$cpt) {
		$regex_nb_numeros='#<li><A HREF="publication.php\?c='.$pays.'/'.$magazine.'">[^<]+</A>&nbsp;<i>\(([^ ]+) num#';
		preg_match($regex_nb_numeros,$buffer,$nb);
			
		array_push($possede_pct,round(100*($cpt/$nb[1])));
		array_push($total_pct,100-round(100*($cpt/$nb[1])));
			
		array_push($possede,$cpt);
		array_push($total,$nb[1]);
		
		array_push($noms_magazines_courts,utf8_encode($magazine));
		$noms_magazines[$j]=$liste_magazines[$magazine];
		$j++;
	}
}
$title = new title(utf8_encode(L::_('possession_numeros')));
$title->set_style( "{font-size: 20px; color: #F24062; font-family:Tuffy; text-align: center;}" );

$bar_stack = new bar_stack();
//$bar_stack->set_colours(array('#FF8000','#04B404'));

$bar_stack_pct = new bar_stack();
$bar_stack_pct->set_colours(array('#FF8000','#04B404'));

foreach ($possede as $index=>$poss) {
	$tmp = new bar_stack_value($poss,'#FF8000');
	$tmp2 = new bar_stack_value(intval($total[$index])-$poss,'#04B404');
	$tmp->set_tooltip($noms_magazines[$index].utf8_encode('<br>'.L::_('numeros_possedes').' : '.$poss.'<br>'.L::_('total').' : '.intval($total[$index])));
	$tmp2->set_tooltip($noms_magazines[$index].utf8_encode('<br>'.L::_('numeros_manquants').' : '.($total[$index]-$poss).'<br>'.L::_('total').' : #total#'));
	$bar_stack->append_stack(array($tmp,$tmp2));

	//$b->set_tooltip('a');
	//$bar_stack->append_stack(array($poss, intval($total[$index])));
}

	
foreach ($possede_pct as $index=>$poss) {
	$tmp = new bar_stack_value($poss,'#FF8000');
	$tmp2 = new bar_stack_value(intval($total_pct[$index]),'#04B404');
	$tmp->set_tooltip($noms_magazines[$index].utf8_encode('<br>'.L::_('numeros_possedes').' : #val#%'));
	$tmp2->set_tooltip($noms_magazines[$index].utf8_encode('<br>'.L::_('numeros_manquants').' : '.(100-$poss).'%'));
	$bar_stack_pct->append_stack(array($tmp,$tmp2));
}

$supertotal=0;
foreach($total as $index=>$total_mag)
	if ($total_mag+$possede[$index]>$supertotal)
		$supertotal=$total_mag;

$bar_stack->set_keys(
array(
	new bar_stack_key('#FF8000', utf8_encode(L::_('numeros_possedes')), 13 ),
	new bar_stack_key('#04B404', utf8_encode(L::_('numeros_references')), 13 )
));

//$bar_stack->set_tooltip('#x_label# : #val# '.utf8_encode(L::_('numeros__graphique')).'<br>'.L::_('total').' : #total# '.utf8_encode(L::_('references')));


$bar_stack_pct->set_keys(
array(
	new bar_stack_key('#FF8000', utf8_encode(L::_('numeros_possedes')), 13 ),
	new bar_stack_key('#04B404', utf8_encode(L::_('numeros_references')), 13 )
));

//$bar_stack_pct->set_tooltip('#x_label# : #val# %' );

$y = new y_axis();
$y->set_range( 0, $supertotal, intval($supertotal/10) );

$y_pct = new y_axis();
$y_pct->set_range( 0, 100, 5 );

$x = new x_axis();
$x->set_labels_from_array($noms_magazines_courts);

$tooltip = new tooltip();
$tooltip->set_hover();
$chart = new open_flash_chart();
$chart->set_title( $title );
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
$taille_graphique=count($possede)<4?240:80+40*count($possede);
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
swfobject.embedSWF("open-flash-chart.swf", "my_chart", "<?php echo $taille_graphique;?>", "380", "9.0.0");
</script>

<script type="text/javascript">

function ofc_ready(){
	parent.$('iframe_graphique').writeAttribute({'width':'<?php echo 80+$taille_graphique;?>px','height':'450px'});
	
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

function load_2()
{
  //alert("loading data_2");
  tmp = findSWF("my_chart");
  x = tmp.load( JSON.stringify(data_2) );
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
<a href="javascript:load_1()"><?php echo L::_('afficher_valeurs_reelles');?></a>
&nbsp;&nbsp;-&nbsp;&nbsp;
<a href="javascript:load_2()"><?php echo L::_('afficher_pourcentages');?></a>
</body>
</html>
