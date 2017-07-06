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
    	<div id="num_courant" style="top:0; left:90%;position:fixed;width:10%;border:1px solid black;text-align:center;background-color:white">
    		Aucun num&eacute;ro.
    	</div>
       	<div style="width:90%">
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '..');
include_once('../IntervalleValidite.class.php');
include_once('../Inducks.class.php');
include_once('../Edge.class.php');
include_once('../Database.class.php');

if (isset($_GET['wanted'])) {
    if (!is_numeric($_GET['wanted']) || $_GET['wanted'] > 30) {
        die ('Valeur du wanted invalide');
    }
    $requete_plus_demandes="
      SELECT Count(Numero) as cpt, Pays, Magazine, Numero
      FROM numeros ";

    if (isset($_GET['user'])) {
        $id_user=DM_Core::$d->user_to_id($_GET['user']);
        $l=DM_Core::$d->toList($id_user);
        $requete_plus_demandes.=" WHERE CONCAT(Pays, '/', Magazine, ' ', Numero) IN (
            SELECT CONCAT(Pays, '/', Magazine, ' ', Numero) FROM numeros
            WHERE ID_Utilisateur=$id_user) ";
    }

    $requete_plus_demandes.= 'GROUP BY Pays,Magazine,Numero ORDER BY cpt DESC, Pays, Magazine, Numero';
    $resultat_plus_demandes=DM_Core::$d->requete_select($requete_plus_demandes);
    $cpt=-1;
    $cptwanted=0;

    echo '--- WANTED ---';
	$numeros_demandes= [];
	foreach($resultat_plus_demandes as $num) {
		$pays=$num['Pays'];
		$magazine=$num['Magazine'];
		$numero=$num['Numero'];
		$cpt=$num['cpt'];
		
		list($magazine,$numero)=Inducks::get_vrais_magazine_et_numero($pays, $magazine, $numero);
		$publicationcode = $pays.'/'.$magazine;
        $requete_est_dispo = $requete_tranches_pretes_magazine='SELECT 1 FROM tranches_pretes WHERE publicationcode=\''.$publicationcode.'\' AND issuenumber=\''.$numero.'\'';
        $est_dispo=count(DM_Core::$d->requete_select($requete_est_dispo)) > 0;
        if (!$est_dispo) {
			$numeros_demandes[]= ['cpt'=>$cpt, 'publicationcode'=>$publicationcode,'numero'=>$numero];
			if ($cptwanted++ >= $_GET['wanted'])
				break;
		}
    }
	$publicationcodes= [];
    foreach($numeros_demandes as $numero_demande) {
		$publicationcodes[]=$numero_demande['publicationcode'];
	}
	$publicationcodes=array_unique($publicationcodes);
	$liste_magazines = Inducks::get_noms_complets_magazines($publicationcodes);

	foreach($numeros_demandes as $numero_demande) {
		$publicationcode=$numero_demande['publicationcode'];
		list($pays,$magazine)=explode('/',$publicationcode);
		$numero=$numero_demande['numero'];
		$cpt=$numero_demande['cpt'];
		
		$nom_magazine_complet = $liste_magazines[$publicationcode];
		if (is_null($nom_magazine_complet)) {
			$nom_magazine_complet = $publicationcode;
		}
        $est_possede = isset($l) && $l->est_possede($pays,$magazine,$numero);
		?><br /><u><?=$cpt?> utilisateurs <?=($est_possede ? "<b><i>dont {$_GET['user']}</i></b>" : "")?> poss&egrave;dent le num&eacute;ro :</u><br />
		&nbsp;
			<img src="../images/flags/<?=$pays?>.png" /> 
			<?=$nom_magazine_complet?> n&deg;<?=$numero?>
		<br /><?php
	}
}
else {
	?><a href="avancement.php?wanted=20">Voir les 20 tranches les plus demand&eacute;es</a><?php
}
?><hr /><?php

$requete_pays_magazines_tranches_pretes='SELECT DISTINCT publicationcode FROM tranches_pretes ORDER BY publicationcode';

$resultat_pays_magazines_tranches_pretes=DM_Core::$d->requete_select($requete_pays_magazines_tranches_pretes);

$cpt_dispos=0;
$publicationcodes= [];
foreach($resultat_pays_magazines_tranches_pretes as $publicationcode) {
	$publicationcodes[]=$publicationcode['publicationcode'];
}
$liste_magazines = Inducks::get_noms_complets_magazines($publicationcodes);
$numeros_inducks=Inducks::get_numeros_liste_publications($publicationcodes);
foreach($resultat_pays_magazines_tranches_pretes as $infos_numero) {
	$publicationcode=$infos_numero['publicationcode'];
	list($pays,$magazine)=explode('/',$publicationcode);
	echo '<br /><br />(<img src="../images/flags/'.$pays.'.png" /> '.$magazine.') '.$liste_magazines[$publicationcode].'<br />';
	$requete_tranches_pretes_magazine='SELECT issuenumber FROM tranches_pretes WHERE publicationcode=\''.$publicationcode.'\'';
	$resultat_tranches_pretes_magazine=DM_Core::$d->requete_select($requete_tranches_pretes_magazine);
	$tranches_pretes= [];
	foreach($resultat_tranches_pretes_magazine as $tranche_prete_magazine) {
		$tranches_pretes[]=$tranche_prete_magazine['issuenumber'];
	}
	if (array_key_exists($publicationcode, $numeros_inducks)) {
		foreach($numeros_inducks[$publicationcode] as $numero_inducks) {
			$tranche_prete_numero_inducks = in_array($numero_inducks,$tranches_pretes);
			?><span onmouseover="document.getElementById('num_courant').innerHTML='<?=$liste_magazines[$publicationcode].' '.$numero_inducks?>';"
			class="num bordered <?=$tranche_prete_numero_inducks?'dispo':''?>">&nbsp;</span><?php
			if ($tranche_prete_numero_inducks)
				$cpt_dispos++;
		}
	} else {
		?>Certaines tranches de cette publication sont prêtes mais la publication n'existe plus sur Inducks : <?=implode(', ', $tranches_pretes)?><?php
	}
}


	?><br  /><br />
	<b><?=$cpt_dispos?> tranches pr&ecirc;tes.</b><br />
        <br /><br />
        <u>L&eacute;gende : </u><br />
        <span class="num">&nbsp;</span> Nous avons besoin d'une photo de cette tranche !<br />

        <span class="num dispo">&nbsp;</span> Cette tranche est pr&ecirc;te.<br />

        </div>
    </body>
</html>
