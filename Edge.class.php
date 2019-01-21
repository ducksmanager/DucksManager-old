<?php
include_once 'DucksManager_Core.class.php';

class Edge {
	var $pays;
	var $magazine;
	var $numero;
	var $numero_reference;
	var $est_visible=true;
	var $html='';

	static $grossissement_defaut=1.5;
	static $sans_etageres = false;

    function __construct($pays = null, $magazine = null, $numero = null, $numero_reference = null, $visible = null, $small = false) {
		if (is_null($pays)) {
            return;
        }
		$this->pays=$pays;
		$this->magazine=$magazine;
		$this->numero= $numero;
		$this->numero_reference= $numero_reference;

        $this->est_visible = $visible;
        $this->html=$this->getImgHTML($small);
    }
	
	function getImgHTML($small) {
        ob_start();
        ?><img data-edge="<?=$this->est_visible ? 1 : 0?>"
             class="tranche"
             name="<?=$this->pays?>/<?=$this->magazine?>.<?=$this->numero_reference?>"
             id="<?=$this->pays?>/<?=$this->magazine?>.<?=$this->numero?>"
             <?=$small ? 'onload="this.height*=0.75;this.onload=null"' : '' ?>
        /><?php
        return ob_get_clean();
	}

    static function getPointsPhotographeAGagner($id_user){
        $requete_points_tranche = "
            SELECT np.Pays, np.Magazine, np.Numero, np.Popularite
            FROM numeros
            INNER JOIN numeros_popularite np USING(Pays, Magazine, Numero)
            WHERE numeros.ID_Utilisateur=$id_user
            ORDER BY np.Popularite DESC
        ";

        $resultats_points_tranches = DM_Core::$d->requete($requete_points_tranche);
        return array_map(function($tranche) {
            $tranche['Popularite'] = (int)$tranche['Popularite'];
            return $tranche;
        }, $resultats_points_tranches);
    }

	static function getBibliotheque($id_user) {
		$requete_tranches = "
            SELECT numeros.Pays,
                   numeros.Magazine,
                   numeros.Numero,
                   IFNULL(reference.NumeroReference, REPLACE(numeros.Numero, ' ', '')) AS NumeroReference,
                   EXISTS(
                       SELECT 1
                       FROM tranches_pretes
                       WHERE CONCAT(numeros.Pays, '/', numeros.Magazine) = tranches_pretes.publicationcode
                         AND IFNULL(reference.NumeroReference, REPLACE(numeros.Numero, ' ', '')) = tranches_pretes.issuenumber
                     ) AS has_edge
            FROM numeros
            LEFT JOIN tranches_doublons reference ON numeros.Pays = reference.Pays AND numeros.Magazine = reference.Magazine AND REPLACE(numeros.Numero, ' ', '') = reference.Numero
            WHERE ID_Utilisateur = ?
            ORDER BY numeros.Pays, numeros.Magazine, numeros.Numero";

		$resultats_tranches = DM_Core::$d->requete($requete_tranches, [$id_user]);
		$resultats_tranches_avec_cle = [];
		foreach($resultats_tranches as $resultat) {
            $resultats_tranches_avec_cle[$resultat['Pays'].'/'.$resultat['Magazine']][$resultat['Numero']] = $resultat;
        }
        $total=count($resultats_tranches);

        $texte_final='';
        $cpt_tranches_pretes=0;

        $publication_codes = array_unique(array_map(function($numero) {
            return $numero['Pays'].'/'.$numero['Magazine'];
        }, $resultats_tranches));
        
        $publication_codes_pour_verif_ordre = array_values(array_filter($publication_codes, function($publicationCode) use ($resultats_tranches) {
            return count(array_filter($resultats_tranches, function($numero) use ($publicationCode) {
                return $numero['Pays'].'/'.$numero['Magazine'] === $publicationCode && !preg_match('#^\d$#', $numero['Numero']); // Ignorer les numéros à un seul chiffre
            }));
        }));

        $resultats_ordres_numeros = Inducks::requete('
          SELECT publicationcode, REGEXP_REPLACE(issuenumber, "[ ]+", " ") AS issuenumber
          FROM inducks_issue
          WHERE publicationcode IN ('. implode(',', array_fill(0, count($publication_codes_pour_verif_ordre), '?')) .')',
            $publication_codes_pour_verif_ordre
        );

        foreach($publication_codes as $publication_code) {
            if (in_array($publication_code, $publication_codes_pour_verif_ordre, true)) {
                $numeros_indexes = array_values(array_filter($resultats_ordres_numeros, function($resultat) use ($publication_code) {
                    return $resultat['publicationcode'] === $publication_code;
                }));
            }
            else {
                $numeros_indexes = array_map(function($numero) use ($publication_code) {
                    return ['publicationcode' => $publication_code, 'issuenumber' => $numero['Numero']];
                }, array_values(array_filter($resultats_tranches, function($numero) use ($publication_code) {
                    return $numero['Pays'].'/'.$numero['Magazine'] === $publication_code;
                })));
            }
            foreach($numeros_indexes as $numero_indexe) {
                if (!empty($resultats_tranches_avec_cle[$numero_indexe['publicationcode']])
                 && !empty($numero = $resultats_tranches_avec_cle[$numero_indexe['publicationcode']][$numero_indexe['issuenumber']])) {
                    $e=new Edge($numero['Pays'], $numero['Magazine'], $numero['Numero'], $numero['NumeroReference'], $numero['has_edge'] === '1');
                    if ($e->est_visible) {
                        $cpt_tranches_pretes++;
                    }
                    $texte_final.=$e->html;
                }
            }
        }
        $pourcentage_visible=$total===0 ? 0 : (int)(100 * $cpt_tranches_pretes / $total);
        return [$texte_final, $pourcentage_visible, Inducks::get_noms_complets_magazines($publication_codes)];
	}

    static function get_user_bibliotheque($user) {
        if ($user === '-1') {
            $id_user = $_SESSION['id_user'];
        }
        else {
            $resultats_utilisateur = DM_Core::$d->requete("SELECT ID, AccepterPartage FROM users WHERE username='$user'");
            if ($resultats_utilisateur[0]['AccepterPartage'] === '1') {
                return $resultats_utilisateur[0]['ID'];
            }
            else return null;
        }
        return $id_user;
    }

    static function get_lien_bibliotheque($user) {
        return "https://www.ducksmanager.net/?action=bibliotheque&user={$user}";
    }

}

if (isset($_POST['get_points'])) {
    $nb_points_courants = DM_Core::$d->get_points_courants($_SESSION['id_user']);
    echo json_encode(['points' => $nb_points_courants]);
}
elseif (isset($_POST['get_popularite_numeros'])) {
    header('Content-type: application/json');
    echo json_encode([
        'popularite_numeros' => Edge::getPointsPhotographeAGagner($_SESSION['id_user'])
    ]);
}
elseif (isset($_POST['get_bibliotheque'])) {
    header('Content-type: application/json');
    $user = $_POST['user_bibliotheque'];
    $id_user = Edge::get_user_bibliotheque($user);

    if (is_null($id_user)) {
        echo json_encode(['erreur' => 'La bibliothèque de cet utilisateur est privée.']);
    }
    else {
        $requete_grossissement = '
          SELECT Bibliotheque_Texture1, Bibliotheque_Sous_Texture1, Bibliotheque_Texture2, Bibliotheque_Sous_Texture2
          FROM users
          WHERE ID = ?';
        $resultats_params_bibliotheque = DM_Core::$d->requete($requete_grossissement, [$id_user]);
        $textures = [
            [
                'texture' => $resultats_params_bibliotheque[0]['Bibliotheque_Texture1'],
                'sous_texture' => $resultats_params_bibliotheque[0]['Bibliotheque_Sous_Texture1']
            ],
            [
                'texture' => $resultats_params_bibliotheque[0]['Bibliotheque_Texture2'],
                'sous_texture' => $resultats_params_bibliotheque[0]['Bibliotheque_Sous_Texture2']
            ],
        ];

        [$html, $pourcentage_visible, $liste_magazines] = Edge::getBibliotheque($id_user);

        echo json_encode([
            'titre' => $user === '-1' ? BIBLIOTHEQUE_COURT : (BIBLIOTHEQUE_DE . $user),
            'nb_numeros_visibles' => $pourcentage_visible,
            'contenu' => $html,
            'textures' => $textures,
            'noms_magazines' => $liste_magazines
        ]);
    }
}

elseif (isset($_POST['get_sous_textures'])) {
	$id_user=$_SESSION['id_user'];
	$requete_texture="
	    SELECT Bibliotheque_Sous_Texture1 as texture1, Bibliotheque_Sous_Texture2 as texture2
	    FROM users
	    WHERE ID = '$id_user'
	";
	$resultat_texture=DM_Core::$d->requete($requete_texture);
    $bookcaseData = ['textures' => [], 'current' => [$resultat_texture[0]['texture1'], $resultat_texture[0]['texture2']]];

	$rep = 'edges/textures/bois';
	$dir = opendir($rep);
	while ($f = readdir($dir)) {
		if( $f!=='.' && $f!=='..') {
			$nom_sous_texture=substr($f,0, strrpos($f, '.'));
            $bookcaseData['textures'][]=$nom_sous_texture;
		}
	}
	sort($bookcaseData['textures']);

    $bookcaseData['sorts'] = DmClient::get_service_results_for_dm('GET', '/collection/bookcase/sort');
    $bookcaseData['publicationNames'] = Inducks::get_noms_complets_magazines($bookcaseData['sorts']);

    header('Content-type: application/json');
    echo json_encode($bookcaseData);
}
elseif (isset($_POST['partager_bibliotheque'])) {
    Affichage::partager_page();
}

?>
