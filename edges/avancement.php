<?php header("Content-Type: text/html; charset=UTF-8"); ?>
<html>
    <head>
        <style type="text/css">
            .num {
                width:4px;
                background-color: red;
            }
            
            .num.dispo {
           		background-color: green;
            }
            
            .bordered {
                border-right:1px solid black;
            }
        </style>
    </head>
    <body>
    	<div id="num_courant" style="top:0px; left:90%;position:fixed;width:10%;border:1px solid black;text-align:center;background-color:white">
    		Aucun num&eacute;ro.
    	</div>
       	<div style="width:90%">
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '..');
$a=get_include_path();
include_once('../IntervalleValidite.class.php');
include_once('../Inducks.class.php');
include_once('../Edge.class.php');
include_once('../Database.class.php');

if (isset($_GET['wanted'])) {
    if (!is_numeric($_GET['wanted']) || $_GET['wanted'] > 30) {
        die ('Valeur du wanted invalide');
    }
    echo '--- WANTED ---';
    $requete_plus_demandes='SELECT Count(Numero) as cpt, Pays, Magazine, Numero '
                          .'FROM numeros '
                          .'GROUP BY Pays,Magazine,Numero ORDER BY cpt DESC, Pays, Magazine, Numero LIMIT 1500';
    $resultat_plus_demandes=DM_Core::$d->requete_select($requete_plus_demandes);
    $cpt=-1;
    $cptwanted=0;
    foreach($resultat_plus_demandes as $numero) {
        $e = new Edge($numero['Pays'],$numero['Magazine'],$numero['Numero'],$numero['Numero']);
		$est_dispo=$e->est_visible;
		if (!$est_dispo) {  
			list($nom_pays_complet,$nom_magazine_complet)=Inducks::get_nom_complet_magazine($numero['Pays'], $numero['Magazine']);
			?><br /><u><?=$numero['cpt']?> demandes pour :</u><br />
			&nbsp;
				<?=$numero['Pays']?> <?=utf8_decode($nom_magazine_complet)?> n&deg;<?=$numero['Numero']?>
			<br /><?php
			if ($cptwanted++ >= $_GET['wanted'])
				break;
			$cpt=$numero['cpt'];
		}
    }
}
?><hr /><?php

$requete_pays_magazines_tranches_pretes='SELECT DISTINCT publicationcode FROM tranches_pretes ORDER BY publicationcode';

$resultat_pays_magazines_tranches_pretes=DM_Core::$d->requete_select($requete_pays_magazines_tranches_pretes);

$cpt_dispos=0;
$publicationcodes=array();
foreach($resultat_pays_magazines_tranches_pretes as $publicationcode) {
	$publicationcodes[]=$publicationcode['publicationcode'];
}
list($liste_pays,$liste_magazines)=Inducks::get_noms_complets($publicationcodes);
$numeros_inducks=Inducks::get_numeros_liste_publications($publicationcodes);
foreach($resultat_pays_magazines_tranches_pretes as $infos_numero) {
	$publicationcode=$infos_numero['publicationcode'];
	list($pays,$magazine)=explode('/',$publicationcode);
	echo '<br /><br />(<img src="../images/flags/'.$pays.'.png" /> '.$magazine.') '.$liste_magazines[$publicationcode].'<br />';
	$requete_tranches_pretes_magazine='SELECT issuenumber FROM tranches_pretes WHERE publicationcode=\''.$publicationcode.'\'';
	$resultat_tranches_pretes_magazine=DM_Core::$d->requete_select($requete_tranches_pretes_magazine);
	$tranches_pretes=array();
	foreach($resultat_tranches_pretes_magazine as $tranche_prete_magazine) {
		$tranches_pretes[]=$tranche_prete_magazine['issuenumber'];
	}
	foreach($numeros_inducks[$publicationcode] as $numero_inducks) {
		$tranche_prete_numero_inducks = in_array($numero_inducks,$tranches_pretes);
		?><span onmouseover="document.getElementById('num_courant').innerHTML='<?=$liste_magazines[$publicationcode].' '.$numero_inducks?>';"
		class="num bordered <?=$tranche_prete_numero_inducks?'dispo':''?>">&nbsp;</span><?php
		if ($tranche_prete_numero_inducks)
			$cpt_dispos++;
	}
}


?><br  />
		<?=$cpt_dispos?> tranches pr&ecirc;tes.<br />
        <br /><br />
        <u>L&eacute;gende : </u><br />
        <span class="num">&nbsp;</span> Nous avons besoin d'une photo de cette tranche !<br />

        <span class="num dispo">&nbsp;</span> Cette tranche est pr&ecirc;te.<br />

        </div>
    </body>
</html>