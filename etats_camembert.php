<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Database.class.php');
require_once('Inducks.class.php');

$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$resultat=DM_Core::$d->requete_select('SELECT Count(Numero) AS c FROM numeros WHERE ID_Utilisateur='.$id_user);
$total=$resultat[0]['c'];
$autres=0;
$valeurs_magazines=array();
$codes_couleur=array();
foreach(Database::$etats as $etat_court=>$infos_etat) {
	$resultat=DM_Core::$d->requete_select('SELECT Count(Numero) AS c FROM numeros WHERE ID_Utilisateur='.$id_user.' AND Etat LIKE \''.$etat_court.'\'');
	$cpt=$resultat[0]['c'];
	if ($cpt==0) continue;
	if ($cpt/$total<0.01) {
		$autres+=$cpt;
	}
	else {
		$titre=utf8_encode(Database::$etats[$etat_court][0]);
		$codes_couleur[]=Database::$etats[$etat_court][1];
		$valeur=$cpt*100;
		$valeurs_magazines[]=array($titre,$valeur);
	}
}
if ($autres!=0) {
	$titre_autres=utf8_encode(AUTRES);
	$codes_couleur[]='#FFFFFF';
	$valeur_autres=$autres*100;
	$valeurs_magazines[]=array($titre_autres,$valeur_autres);
}

$titre=utf8_encode(ETATS_MAGAZINES);
?>

<html>
	<head>
		<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="jqplot/excanvas.js"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="jqplot/jquery.min.js"></script>
		<script language="javascript" type="text/javascript" src="jqplot/jquery.jqplot.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.pieRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.donutRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.highlighter.js"></script>
		<link rel="stylesheet" type="text/css" href="jqplot/jquery.jqplot.css" />
		<style type="text/css">
			body {
				background-color:#F8F8D8;
			}
			#chart {
				width: 400px;
				height: 400px;
			}
			
			.jqplot-title {
				color: black;
				font-size: 0.9em;
			}
		</style>
		<script type="text/javascript">
		var plot1;
			$(document).ready(function(){
				var data = [
				    <?php foreach($valeurs_magazines as $titre_valeur) {
				    		?>['<?=$titre_valeur[0]?>',<?=$titre_valeur[1]?>],<?php
				    }?>
				];
				  
				plot1 = jQuery.jqplot ('chart', [data], 
				{ 
					grid: {
						background: '#F8F8D8'
					},
					title:'<?=$titre?>',
					seriesColors:["<?=implode('","',$codes_couleur)?>"],
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer, 
						rendererOptions: {
							diameter: 250,
							showDataLabels: true,  
				            varyBarColor : true,
							dataLabels:'percent'
						}
					}, 
					legend: { show:true, location: 'e' }
				});
			});
		</script>
	</head>
	<body>
		<div id="chart"></div>
	</body>
</html>