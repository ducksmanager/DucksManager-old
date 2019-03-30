<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<html>
    <head>
        <meta charset="UTF-8" />
        <style type="text/css">
            .publication {
                margin-top: 20px;
            }
            .num {
                width:4px;
                cursor: default;
            }

            .num, #num_courant {
                background-color: red;
            }
            
            .dispo {
           		background-color: green !important;
            }

            .num.dispo {
                cursor: pointer;
            }
            
            .bordered {
                border-right:1px solid black;
            }

            #num_courant {
                position: fixed;
                top: 0;
                left: 90%;
                width: 10%;
                border: 1px solid black;
                text-align: center;
            }

            #num_courant.init {
                background-color: white !important;
            }
        </style>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script type="text/javascript">
            $(function() {
                $('.num.bordered')
                    .mouseover(function() {
                        var publication = $(this).closest('.publication');
                        var publicationName = publication.find('.publication_name').html();
                        var issueNumber = $(this).attr('title');

                        $('#num_courant')
                            .removeClass('init')
                            .toggleClass('dispo', $(this).hasClass('dispo'))
                            .html(publicationName + ' ' + issueNumber);
                    })
                    .filter('.dispo').click(function() {
                        var publication = $(this).closest('.publication');
                        var issueNumber = $(this).attr('title');

                        window.open('https://edges.ducksmanager.net/edges/' + publication.data('country') + '/gen/' + publication.data('magazine') + '.' + issueNumber + '.png', '_blank');
                    });
            })
        </script>
    </head>
    <body>
    	<div id="num_courant" class="init">
    		Aucun numéro
    	</div>
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '..');
include_once '../Inducks.class.php';
include_once '../Edge.class.php';
include_once '../Database.class.php';

$show = isset($_GET['show']);
if ($show) {
    include_once '../authentification.php';
}

if (isset($_GET['wanted'])) {
    if (!is_numeric($_GET['wanted']) || $_GET['wanted'] > 30) {
        die ('Valeur du wanted invalide');
    }
    $requete_plus_demandes="
        SELECT Count(Numero) as cpt, CONCAT(Pays,'/',Magazine) AS publicationcode, Numero
        FROM numeros
        WHERE NOT EXISTS(
            SELECT 1
            FROM tranches_pretes
            WHERE CONCAT(numeros.Pays, '/', numeros.Magazine) = tranches_pretes.publicationcode
              AND numeros.Numero_nospace = tranches_pretes.issuenumber
        )
        GROUP BY Pays,Magazine,Numero
        ORDER BY cpt DESC, Pays, Magazine, Numero
        LIMIT {$_GET['wanted']}";

    if (isset($_GET['user'])) {
        $id_user=DM_Core::$d->user_to_id($_GET['user']);
        $requete_plus_demandes="
            SELECT
              (select Count(Numero) AS cpt from numeros tot where numeros.Pays = tot.Pays and numeros.Magazine = tot.Magazine and numeros.Numero = tot.Numero) as cpt,
              Pays,
              Magazine,
              Numero
            FROM numeros
            WHERE ID_Utilisateur = $id_user
            ORDER BY cpt DESC, Pays, Magazine, Numero
        ";
        $resultat_plus_demandes=DM_Core::$d->requete($requete_plus_demandes);
    }
    else {
        $resultat_plus_demandes=DM_Core::$d->requete($requete_plus_demandes, [(int)$_GET['wanted']]);
    }

    $cpt=-1;
    $cptwanted=0;

    echo '--- WANTED ---';
    $publicationcodes = array_unique(array_map(function($publicationcode) {
        return $publicationcode['publicationcode'];
    }, $resultat_plus_demandes));
	$liste_magazines = Inducks::get_noms_complets_magazines($publicationcodes);

	foreach($resultat_plus_demandes as $numero_demande) {
		$publicationcode=$numero_demande['publicationcode'];
		[$pays,$magazine] =explode('/',$publicationcode);
		$numero=$numero_demande['Numero'];
		$cpt=$numero_demande['cpt'];

		$nom_magazine_complet = $liste_magazines[$publicationcode];
		if (is_null($nom_magazine_complet)) {
			$nom_magazine_complet = $publicationcode;
		}
		?><div>
            <div>
                <u><?=$cpt?> utilisateurs <?=isset($_GET['user']) ? "<b><i>dont {$_GET['user']}</i></b>" : ""?> possèdent le numéro :</u>
            </div>		&nbsp;
            <img src="../images/flags/<?=$pays?>.png" />
            <?=$nom_magazine_complet?> n°<?=$numero?>
        </div><?php
	}
}
else {
	?><a href="avancement.php?wanted=20">Voir les 20 tranches les plus demandées</a><?php
}
?><hr /><?php

$requete_pays_magazines_tranches_pretes='SELECT DISTINCT publicationcode FROM tranches_pretes ORDER BY publicationcode';
$resultat_pays_magazines_tranches_pretes=DM_Core::$d->requete($requete_pays_magazines_tranches_pretes);

$publicationcodes = array_map(function($publicationcode) {
	return $publicationcode['publicationcode'];
}, $resultat_pays_magazines_tranches_pretes);

$liste_magazines = Inducks::get_noms_complets_magazines($publicationcodes);
$numeros_inducks=Inducks::get_liste_numeros_from_publicationcodes($publicationcodes);
$requete_tranches_pretes = '
  SELECT publicationcode, issuenumber
  FROM tranches_pretes';

$resultat_tranches_pretes = DM_Core::$d->requete($requete_tranches_pretes);
$tranches_pretes = [];
array_walk($resultat_tranches_pretes, function($resultat) use (&$tranches_pretes) {
    $tranches_pretes[$resultat['publicationcode']][] = $resultat['issuenumber'];
});

$cpt_dispos=0;
foreach($publicationcodes as $publicationcode) {
	[$pays,$magazine] =explode('/',$publicationcode);
    ?><div class="publication" data-country="<?=$pays?>" data-magazine="<?=$magazine?>">
        <span class="publication_name">
            (<img src="../images/flags/<?=$pays?>.png" /> <?=$magazine?>)
            <?=$liste_magazines[$publicationcode]?>
        </span>
        <div><?php

            if (array_key_exists($publicationcode, $numeros_inducks)) {
                foreach($numeros_inducks[$publicationcode] as $numero_inducks) {
                    $tranche_prete_numero_inducks = in_array($numero_inducks,$tranches_pretes[$publicationcode]);
                    if ($tranche_prete_numero_inducks) {
                        $cpt_dispos++;
                    }
                    if ($show) {
                        if ($tranche_prete_numero_inducks) {
                            ?><img src="<?="https://edges.ducksmanager.net/edges/$pays/gen/$magazine.$numero_inducks.png"?>" /><?php
                        }
                        else {
                            ?><span class="num bordered" title="<?=$numero_inducks?>">&nbsp;</span><?php
                        }
                    }
                    else {
                        ?><span class="num bordered <?=$tranche_prete_numero_inducks?'dispo':''?>" title="<?=$numero_inducks?>">&nbsp;</span><?php
                    }
                }
            } else {
                ?>Certaines tranches de cette publication sont prêtes mais la publication n'existe plus sur Inducks : <?=implode(', ', $tranches_pretes)?>
            <?php }
        ?></div>
    </div>
<?php } ?>


	<br  /><br />
	<b><?=$cpt_dispos?> tranches prêtes.</b><br />
        <br /><br />
        <u>Légende : </u><br />
        <span class="num">&nbsp;</span> Nous avons besoin d'une photo de cette tranche !<br />

        <span class="num dispo">&nbsp;</span> Cette tranche est prête.<br />

    </body>
</html>
