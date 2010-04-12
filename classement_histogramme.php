<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('FirePHPCore/FirePHP.class.php');

$GLOBALS['firephp'] = FirePHP::getInstance(true);
$GLOBALS['firephp']->registerErrorHandler(
            $throwErrorExceptions=true);
$GLOBALS['firephp']->registerExceptionHandler();
$GLOBALS['firephp']->registerAssertionHandler(
            $convertAssertionErrorsToExceptions=true,
            $throwAssertionExceptions=false);
ob_start();
require_once('artichow/LinePlot.class.php');
require_once('artichow/BarPlot.class.php');
require_once('Database.class.php');
require_once('Inducks.class.php');


$d=new Database();
if (!$d) {
	echo L::_('probleme_bd');
	exit(-1);
}
$_SESSION['user']='nonoox';
$_SESSION['lang']='fr';
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
	return false;
}
$regex_pays='#<a href=country\.php\?c=([^>]+)>([^<]+)</a>#i';
$liste_pays=array();
preg_match_all($regex_pays,$buffer,$liste_pays);
foreach ($liste_pays[0] as $pays) {
	$liste_pays[preg_replace($regex_pays,'$1',$pays)]=utf8_decode(preg_replace($regex_pays,'$2',$pays));
}
$possede=array();
$total=array();
$noms_magazines=array();
$noms_magazines_courts=array();

$l=$d->toList($id_user);
$counts=array();
foreach($l->collection as $pays=>$numeros_pays) {
	$counts[$pays]=array();
	foreach($numeros_pays as $magazine=>$numeros) {
		$counts[$pays][$magazine]=count($numeros);
	}
}
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
		if (isset($_GET['pct'])) {
			array_push($possede,100*($cpt/$nb[1]));
			array_push($total,100);
		}
		else {
			array_push($possede,$cpt);
			array_push($total,$nb[1]);
		}
		array_push($noms_magazines_courts,$magazine);
		$noms_magazines[$magazine]=$liste_magazines[$magazine].' ['.$cpt.' '.($cpt==1?L::_('numero'):L::_('numeros')).' / '.$nb.' '.L::_('references').']';
		$a=1;
	}
}

$graph = new Graph(700, 380+count($noms_magazines_courts)*20);
$graph->setAntiAliasing(true);
$graph->shadow->setPosition(Shadow::RIGHT_BOTTOM);

$graph->setBackgroundGradient(
	new LinearGradient(
		new Color(240, 240, 240, 0),
		new White,
		0
	)
);


$graph->title->set(L::_('possession_numeros'));
$graph->title->setFont(new Tuffy(15));
$graph->title->setColor(new Color(0x00, 0x00, 0x8B));

$group = new PlotGroup;
$group->setAbsSize(700, 300);
$group->setAbsPosition(5, count($noms_magazines)*20);
$group->setPadding(35, 26, 40, 27);
$group->setSpace(2, 2);

$group->grid->setColor(new Color(0xC4, 0xC4, 0xC4));
$group->grid->setType(Line::DASHED);
$group->grid->hideVertical(TRUE);
$group->grid->setBackgroundColor(new White);

$group->axis->left->setColor(new DarkGreen);
$group->axis->left->label->setFont(new Font2);

$group->axis->right->setColor(new DarkBlue);
$group->axis->right->label->setFont(new Font2);

$group->axis->bottom->label->setFont(new Font2(6));
$group->axis->bottom->setLabelText($noms_magazines_courts);

$group->legend=new Legend(Legend::MODEL_BOTTOM);
$group->legend->setTextFont(new Tuffy(8));
$group->legend->setSpace(10);
$group->legend->setPosition(NULL, 1);

$x = array(20, 25, 20, 18, 16, 25, 29, 12, 15, 18, 21, 26);

$plot = new BarPlot($total, 2, 2);
$plot->setBarColor(new Color(120, 175, 80, 10));
$plot->setBarPadding(0.15, 0.15);
$plot->barShadow->smooth(TRUE);
$plot->barShadow->setColor(new Color(200, 200, 200, 10));

$group->legend->add($plot, L::_('numeros_references'), Legend::BACKGROUND);
$group->add($plot);

// Add a second bar plot
$x = array(12, 14, 10, 9, 10, 16, 12, 8, 8, 10, 12, 13);

$plot = new BarPlot($possede, 2, 2);
$plot->setBarColor(new Orange);
$plot->setBarPadding(0.15, 0.15);

$group->legend->add($plot, L::_('numeros_possedes'), Legend::BACKGROUND);
$group->add($plot);

$graph->add($group);

$i=0;
foreach ($noms_magazines as $nom_court=>$nom_long) {
	$label=new Label($nom_court.' : '.$nom_long);
	$label->setAlign(Label::LEFT);
	$graph->addAbslabel($label,new Point(50,50+20*$i));
	$i++;
}
$group->axis->bottom->setLabelText($noms_magazines_courts);
$graph->draw();
?>