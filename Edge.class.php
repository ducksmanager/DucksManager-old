<?php
include_once 'DucksManager_Core.class.php';

class Edge {
    var $pays;
    var $magazine;
    var $numero;
    var $numero_reference;
    var $est_visible=true;
    var $sprite;

    function __construct($pays = null, $magazine = null, $numero = null, $numero_reference = null, $visible = null, $sprite = null) {
        if (is_null($pays)) {
            return;
        }
        $this->pays=$pays;
        $this->magazine=$magazine;
        $this->numero= $numero;
        $this->numero_reference= $numero_reference;
        $this->est_visible = $visible;
        $this->sprite = $sprite;
    }

    function getImgHTML($small = false) {
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

    static function getBibliotheque($id_user, $publication_codes) {
        $requete_tranches = "
            SELECT numeros.Pays,
                   numeros.Magazine,
                   numeros.Numero,
                   IFNULL(reference.NumeroReference, numeros.Numero_nospace) AS NumeroReference,
                   tp.ID AS EdgeID,
                   IF(tp.ID IS NULL, '', GROUP_CONCAT(
                       IF(sprites.Sprite_name is null, '',
                          JSON_OBJECT('name', sprites.Sprite_name, 'version', sprites.Version, 'size', sprites.Sprite_size))
                       ORDER BY sprites.Sprite_size ASC
                   )) AS Sprites
            FROM numeros
            LEFT JOIN tranches_doublons reference
                ON numeros.Pays = reference.Pays AND numeros.Magazine = reference.Magazine AND
                   numeros.Numero_nospace = reference.Numero
            LEFT JOIN tranches_pretes tp
                ON CONCAT(numeros.Pays, '/', numeros.Magazine) = tp.publicationcode
                AND IFNULL(reference.NumeroReference, numeros.Numero_nospace) = tp.issuenumber
            LEFT JOIN (
                SELECT sprites.ID_Tranche, sprites.sprite_name, sprites.Sprite_size, sprite_urls.Version
                FROM tranches_pretes_sprites sprites
                INNER JOIN tranches_pretes_sprites_urls sprite_urls
                    ON sprites.Sprite_name = sprite_urls.Sprite_name
            ) AS sprites
            ON sprites.ID_Tranche = tp.ID
            WHERE ID_Utilisateur = ?
            GROUP BY numeros.Pays, numeros.Magazine, numeros.Numero
            ORDER BY numeros.Pays, numeros.Magazine, numeros.Numero";

        $resultats_tranches = DM_Core::$d->requete($requete_tranches, [$id_user]);

        if (count($resultats_tranches) === 0) {
            return [];
        }

        $USE_SPRITE_FROM_EDGE_PCT=80;
        $spritesToUse = [];

        foreach($resultats_tranches as $resultat) {
            if (!empty($resultat['Sprites'])) {
                $sprites = json_decode("[{$resultat['Sprites']}]", true);
                foreach($sprites as $i=>$sprite) {
                    if (!array_key_exists($sprite['name'], $spritesToUse)) {
                        $spritesToUse[$sprite['name']] = $sprite + ['edges' => []];
                    }
                    $spritesToUse[$sprite['name']]['edges'][] = $resultat['EdgeID'];
                }
            }
        }

        // On ne garde que les sprites dont au moins 80% des tranches sont possédées par l'utilisateur
        $spritesToUse = array_filter($spritesToUse, function($sprite) use ($USE_SPRITE_FROM_EDGE_PCT) {
            return count($sprite['edges']) >= $sprite['size'] * $USE_SPRITE_FROM_EDGE_PCT/100;
        });

        usort($spritesToUse, function($a, $b) {
            return $a['size'] < $b['size'] ? -1 : ($a['size'] > $b['size'] ? 1 : 0);
        });

        $edgesUsingSprites = [];
        foreach($spritesToUse as $sprite) {
            foreach($sprite['edges'] as $edgeId) {
                $edgesUsingSprites[(int)$edgeId] = ['name' => $sprite['name'], 'version' => $sprite['version']];
            }
        }

        $resultats_tranches_par_publication = [];
        foreach($resultats_tranches as $resultat) {
            $resultats_tranches_par_publication[$resultat['Pays'].'/'.$resultat['Magazine']][$resultat['Numero']] = $resultat;
        }

        $edgesData=[];

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
        $resultats_ordres_numeros = array_reduce($resultats_ordres_numeros, function (array $accumulator, array $resultat) {
            $accumulator[$resultat['publicationcode']][] = $resultat['issuenumber'];
            return $accumulator;
        }, []);

        foreach($publication_codes as $publication_code) {
            if (in_array($publication_code, $publication_codes_pour_verif_ordre, true)) {
                $numeros_indexes = array_key_exists($publication_code, $resultats_ordres_numeros)
                    ? $resultats_ordres_numeros[$publication_code]
                    : [];
            }
            else {
                $numeros_indexes = array_map(function($numero) {
                    return $numero['Numero'];
                }, array_values(array_filter($resultats_tranches, function($numero) use ($publication_code) {
                    return $numero['Pays'].'/'.$numero['Magazine'] === $publication_code;
                })));
            }
            foreach($numeros_indexes as $numero_indexe) {
                if (array_key_exists($publication_code, $resultats_tranches_par_publication)
                 && array_key_exists($numero_indexe, $resultats_tranches_par_publication[$publication_code])) {
                    $numero = $resultats_tranches_par_publication[$publication_code][$numero_indexe];
                    $sprite = $edgesUsingSprites[$numero['EdgeID']] ?? null;
                    $edgesData[]=new Edge($numero['Pays'], $numero['Magazine'], $numero['Numero'], $numero['NumeroReference'], !is_null($numero['EdgeID']), $sprite);
                }
            }
        }
        return $edgesData;
    }

    static function get_user_bibliotheque($user) {
        if ($user === '-1') {
            $id_user = $_SESSION['id_user'];
        }
        else {
            $resultats_utilisateur = DM_Core::$d->requete('
              SELECT ID, AccepterPartage
              FROM users
              WHERE username=?'
            , [$user]);
            if ($resultats_utilisateur[0]['AccepterPartage'] === '1') {
                return $resultats_utilisateur[0]['ID'];
            }
            else return null;
        }
        return $id_user;
    }

    static function get_lien_bibliotheque($user) {
        return "https://ducksmanager.net/?action=bibliotheque&user={$user}";
    }

}

if (isset($_POST['get_popularite_numeros'])) {
    if (isset($_SESSION['id_user'])) {
        header('Content-type: application/json');
        echo json_encode([
            'popularite_numeros' => Edge::getPointsPhotographeAGagner($_SESSION['id_user'])
        ]);
    }
}
elseif (isset($_POST['get_bibliotheque'])) {
    header('Content-type: application/json');
    $user = $_POST['user_bibliotheque'];
    $id_user = Edge::get_user_bibliotheque($user);

    if (is_null($id_user)) {
        echo json_encode(['erreur' => 'La bibliothèque de cet utilisateur est privée.']);
    }
    else {
        $requete_textures = '
          SELECT Bibliotheque_Texture1, Bibliotheque_Sous_Texture1, Bibliotheque_Texture2, Bibliotheque_Sous_Texture2
          FROM users
          WHERE ID = ?';
        $resultats_params_bibliotheque = DM_Core::$d->requete($requete_textures, [$id_user]);
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

        $publication_codes = DmClient::get_service_results_for_dm('GET', "/ducksmanager/bookcase/$id_user/sort");
        $edgesData = Edge::getBibliotheque($id_user, $publication_codes);

        echo json_encode([
            'titre' => $user === '-1' ? BIBLIOTHEQUE_COURT : (BIBLIOTHEQUE_DE . $user),
            'edgesData' => $edgesData,
            'textures' => $textures,
            'noms_magazines' => Inducks::get_noms_complets_magazines($publication_codes)
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

    $bookcaseData['sorts'] = DmClient::get_service_results_for_dm('GET', "/ducksmanager/bookcase/$id_user/sort");
    $bookcaseData['publicationNames'] = Inducks::get_noms_complets_magazines($bookcaseData['sorts']);

    header('Content-type: application/json');
    echo json_encode($bookcaseData);
}
elseif (isset($_POST['partager_bibliotheque'])) {
    Affichage::partager_page();
}

?>
