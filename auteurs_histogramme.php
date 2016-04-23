<?php
session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';
require_once ('Database.class.php');
Util::exit_if_not_logged_in();

$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$requete_date_dernier_calcul='SELECT MAX(DateStat) AS dernier_calcul FROM auteurs_pseudos WHERE ID_User='.$id_user;
$resultat_date_dernier_calcul=DM_Core::$d->requete_select($requete_date_dernier_calcul);
$date_dernier_calcul=$resultat_date_dernier_calcul[0]['dernier_calcul'];
if ($date_dernier_calcul=='0000-00-00')
	exit(0);
$requete_auteurs='SELECT NomAuteur, NbNonPossedesFrance, NbNonPossedesEtranger, NbPossedes FROM auteurs_pseudos '
				.'WHERE ID_User='.$id_user.' AND DateStat = \''.$date_dernier_calcul.'\'';
$resultat_auteurs=DM_Core::$d->requete_select($requete_auteurs);
$non_poss_etr= [];$non_poss_etr_pct= [];
$non_poss_fr= [];$non_poss_fr_pct= [];
$poss= [];$poss_pct= [];
$totaux= [];
$auteurs= [];
foreach($resultat_auteurs as $auteur) {
	$total=$auteur['NbNonPossedesEtranger']+$auteur['NbNonPossedesFrance']+$auteur['NbPossedes'];

	if ($total==0) {
            array_push($non_poss_etr_pct,0);
            array_push($non_poss_fr_pct,0);
            array_push($poss_pct,0);
        }
        else {
            array_push($non_poss_etr_pct,round(100*$auteur['NbNonPossedesEtranger']/$total));
            array_push($non_poss_fr_pct,round(100*$auteur['NbNonPossedesFrance']/$total));
            array_push($poss_pct,round(100*$auteur['NbPossedes']/$total));
        }
	array_push($non_poss_etr,$auteur['NbNonPossedesEtranger']);
	array_push($non_poss_fr,$auteur['NbNonPossedesFrance']);
	array_push($poss,$auteur['NbPossedes']);

	array_push($auteurs,$auteur['NomAuteur']);
	array_push($totaux,$total);
}
if (count($resultat_auteurs)<=5)
	$largeur=500;
else
	$largeur=100*count($resultat_auteurs);
date_default_timezone_set('UTC');
$regex_date='#([^-]+)-([^-]+)-(.+)#is';
$title = new title(utf8_encode(POSSESSION_HISTOIRES_AUTEURS.' ('
				   .preg_replace($regex_date,'$3/$2/$1',$date_dernier_calcul)
				   .')'));
$title->set_style( "{font-size: 20px; color: #F24062; font-family:Tuffy; text-align: center;}" );

$bar_stack = new bar_stack();
$bar_stack_pct = new bar_stack();
foreach ($poss as $index=>$val_poss) {
	$val_non_poss_fr=$non_poss_fr[$index];
	$val_non_poss_etr=$non_poss_etr[$index];
	$tmp = new bar_stack_value(intval($val_poss),'#FF8000');
	$tmp2 = new bar_stack_value(intval($val_non_poss_fr),'#04B404');
	$tmp3 = new bar_stack_value(intval($val_non_poss_etr),'#C12346');
	$tmp->set_tooltip($auteurs[$index].'<br>'.utf8_encode(HISTOIRES_POSSEDEES.' : '.$val_poss.'<br>'.TOTAL.' : '.$totaux[$index]));
	$tmp2->set_tooltip($auteurs[$index].'<br>'.utf8_encode(HISTOIRES_NON_POSSEDEES_PAYS.' : '.$val_non_poss_fr.'<br>'.TOTAL.' : '.$totaux[$index]));
	$tmp3->set_tooltip($auteurs[$index].'<br>'.utf8_encode(HISTOIRES_NON_POSSEDEES_ETRANGER.' : '.$val_non_poss_etr.'<br>'.TOTAL.' : '.$totaux[$index]));

	$bar_stack->append_stack([$tmp,$tmp2,$tmp3]);
}

foreach ($poss_pct as $index=>$val_poss) {
	$val_non_poss_fr=$non_poss_fr_pct[$index];
	$val_non_poss_etr=$non_poss_etr_pct[$index];
	$tmp = new bar_stack_value($val_poss,'#FF8000');
	$tmp2 = new bar_stack_value($val_non_poss_fr,'#04B404');
	$tmp3 = new bar_stack_value(100-$val_poss-$val_non_poss_fr,'#C12346');
	$tmp->set_tooltip(utf8_encode($auteurs[$index].'<br>'.HISTOIRES_POSSEDEES.' : '.$val_poss.'%<br>'.TOTAL.' : '.$totaux[$index]));
	$tmp2->set_tooltip(utf8_encode($auteurs[$index].'<br>'.HISTOIRES_NON_POSSEDEES_PAYS.' : '.$val_non_poss_fr.'%<br>'.TOTAL.' : '.$totaux[$index]));
	$tmp3->set_tooltip(utf8_encode($auteurs[$index].'<br>'.HISTOIRES_NON_POSSEDEES_ETRANGER.' : '.$val_non_poss_etr.'%<br>'.TOTAL.' : '.$totaux[$index]));

	$bar_stack_pct->append_stack([$tmp,$tmp2,$tmp3]);
}

$supertotal=0;
foreach($totaux as $index=>$total)
	if ($total>$supertotal)
		$supertotal=$total;


$y = new y_axis();
$y->set_range( 0, $supertotal, intval($supertotal/10) );

$y_pct = new y_axis();
$y_pct->set_range( 0, 100, 5 );

$x = new x_axis();
$x->set_labels_from_array($auteurs);

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
<a href="javascript:load_1()"><?php echo AFFICHER_VALEURS_REELLES;?></a>
&nbsp;&nbsp;-&nbsp;&nbsp;
<a href="javascript:load_2()"><?php echo AFFICHER_POURCENTAGES;?></a>
</body>
</html>
