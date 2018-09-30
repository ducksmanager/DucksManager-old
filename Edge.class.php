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

	static function get_numero_clean($numero) {
		return str_replace([' ', '+'], '', $numero);
	}

	static function get_numeros_clean($pays,$magazine,$numeros) {
        $chunk_size = 250;

		$numeros_clean_et_references= [];
		foreach($numeros as $i=>$numero) {
			$numero=$numero[0];
			$numero_clean=self::get_numero_clean($numero);
			$numeros[$i]="'".$numero_clean."'";
			$numeros_clean_et_references[$numero]= [
                'clean'=>$numero_clean,
                'reference'=>$numero_clean,
                'visible' => false
            ];
		}
		
		$numeros_subarrays=array_chunk($numeros, $chunk_size);
		
		foreach($numeros_subarrays as $numeros_subarray) {
			$requete_recherche_numero_reference=
				'SELECT Numero,NumeroReference '
			   .'FROM tranches_doublons '
			   .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero IN ('.implode(',', $numeros_subarray).') ';
			$resultat_numero_reference=DM_Core::$d->requete_select($requete_recherche_numero_reference);
			foreach($resultat_numero_reference as $numero_reference) {
				$numeros_clean_et_references[$numero_reference['Numero']]['reference']
					=$numero_reference['NumeroReference'];
			}
		}

		$vrai_magazine=Inducks::get_vrai_magazine($pays,$magazine);
		if ($vrai_magazine !== $magazine) {
			foreach($numeros_clean_et_references as $numero) {
				$numero_clean = is_array($numero) ? $numero['clean'] : $numero;
				list($vrai_magazine,$vrai_numero) = Inducks::get_vrais_magazine_et_numero($pays,$magazine,$numero_clean);
				$numeros_clean_et_references[$numero_clean]['reference']=$vrai_numero;
			}
		}

        $numeros_references = array_map(function ($numero) {
            return $numero['reference'];
        }, $numeros_clean_et_references);

        $requete_visibilite_numeros = "
              SELECT issuenumber
              FROM tranches_pretes
              WHERE publicationcode = '$pays/$magazine'
                AND issuenumber IN ('" . implode("', '", $numeros_references) . "')";
        $resultat_visibilite_numeros = DM_Core::$d->requete_select($requete_visibilite_numeros);

        $cpt_tranches_pretes=0;
        foreach($resultat_visibilite_numeros as $numero) {
            array_walk($numeros_clean_et_references, function(&$value) use(&$cpt_tranches_pretes, $numero) {
                if ($value['reference'] === $numero['issuenumber']) {
                    $value['visible'] = true;
                    $cpt_tranches_pretes++;
                }
            });
        }

		return [$vrai_magazine, $numeros_clean_et_references, $cpt_tranches_pretes];
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

        $resultats_points_tranches = DM_Core::$d->requete_select($requete_points_tranche);
        return array_map(function($tranche) {
            $tranche['Popularite'] = (int)$tranche['Popularite'];
            return $tranche;
        }, $resultats_points_tranches);
    }

	static function getBibliotheque($id_user) {
		include_once 'Database.class.php';
		@session_start();

        $l=DM_Core::$d->toList($id_user);
        $texte_final='';
        $total_numeros=0;
        $cpt_tranches_pretes=0;
        DM_Core::$d->maintenance_ordre_magazines($id_user);

        // TODO Use DM server service
        $requete_ordre_magazines='SELECT Pays,Magazine,Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' ORDER BY Ordre';
        $resultat_ordre_magazines=DM_Core::$d->requete_select($requete_ordre_magazines);

        $publication_codes = array_map(function($ordre) {
            return $ordre['Pays'].'/'.$ordre['Magazine'];
        }, $resultat_ordre_magazines);

        foreach($resultat_ordre_magazines as $ordre) {
            $pays=$ordre['Pays'];
            $magazine=$ordre['Magazine'];
            $numeros=$l->collection[$pays][$magazine];

            sort($numeros);

            list($magazine, $numeros_clean_et_references, $cpt_tranches_pretes_magazine) = self::get_numeros_clean($pays, $magazine, $numeros);

            $total_numeros+=count($numeros);
            $cpt_tranches_pretes+=$cpt_tranches_pretes_magazine;

            foreach($numeros_clean_et_references as $numero) {
                if (!array_key_exists('clean', $numero)) {
                    $numero['clean'] = $numero['reference'];
                }
                $e=new Edge($pays, $magazine, $numero['clean'], $numero['reference'], $numero['visible']);
                $texte_final.=$e->html;
            }
        }
        $pourcentage_visible=$total_numeros===0 ? 0 : (int)(100 * $cpt_tranches_pretes / $total_numeros);
        return [$texte_final, $pourcentage_visible, Inducks::get_noms_complets_magazines($publication_codes)];
	}

    static function get_user_bibliotheque($user, $cle) {
        if ($user === '-1') {
            $id_user = $_SESSION['id_user'];
        }
        else {
            $id_user = DM_Core::$d->get_id_user_partage_bibliotheque($user, $cle);
        }
        return $id_user;
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
    $id_user = Edge::get_user_bibliotheque($user, $_POST['cle_bibliotheque']);

    if (is_null($id_user)) {
        echo json_encode(['erreur' => 'Lien de partage invalide']);
    }
    else {
        $requete_grossissement = 'SELECT username FROM users WHERE ID = \'' . $id_user . '\'';
        $resultat_grossissement = DM_Core::$d->requete_select($requete_grossissement);
        $username = $resultat_grossissement[0]['username'];

        $textures = [];
        for ($i = 1; $i <= 2; $i++) {
            $requete_textures = 'SELECT Bibliotheque_Texture' . $i . ', Bibliotheque_Sous_Texture' . $i . ' FROM users WHERE ID = \'' . $id_user . '\'';
            $resultat_textures = DM_Core::$d->requete_select($requete_textures);
            $textures[$i - 1] = [
                'texture' => $resultat_textures[0]['Bibliotheque_Texture' . $i],
                'sous_texture' => $resultat_textures[0]['Bibliotheque_Sous_Texture' . $i]
			];
        }

        list($html, $pourcentage_visible, $liste_magazines) = Edge::getBibliotheque($id_user);

        echo json_encode([
            'titre' => $user === '-1' ? BIBLIOTHEQUE_COURT : (BIBLIOTHEQUE_DE . $user),
            'nb_numeros_visibles' => $pourcentage_visible,
            'contenu' => $html,
            'textures' => $textures,
            'noms_magazines' => $liste_magazines
        ]);
    }
}
elseif (isset($_POST['get_texture'])) {
	$id_user=$_SESSION['id_user'];
	$requete_texture='SELECT Bibliotheque_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select($requete_texture);
	$rep = "edges/textures";
	$dir = opendir($rep);
	while ($f = readdir($dir)) {
		if( $f!=='.' && $f!=='..') {
			?>
			<option 
			<?php
			if ($f===$resultat_texture[0]['Bibliotheque_Texture'.$_POST['n']]) {
                echo 'selected="selected" ';
            } ?>
			value="<?=$f?>"
			><?=constant('TEXTURE__'.strtoupper($f))?></option>
			<?php
		}
	}
}

elseif (isset($_POST['get_sous_texture'])) {
	$id_user=$_SESSION['id_user'];
	$requete_texture='SELECT Bibliotheque_Sous_Texture'.$_POST['n'].' FROM users WHERE ID = \''.$id_user.'\'';
	$resultat_texture=DM_Core::$d->requete_select($requete_texture);

	$rep = 'edges/textures/'.$_POST['texture'].'/miniatures';
	$dir = opendir($rep);
	while ($f = readdir($dir)) {
		if( $f!=='.' && $f!=='..') {
			$nom_sous_texture=substr($f,0, strrpos($f, '.'));
			?>
			<option <?php
			if ($nom_sous_texture===$resultat_texture[0]['Bibliotheque_Sous_Texture'.$_POST['n']]) {
                echo 'selected="selected" ';
            } ?>
			style="background:url('edges/textures/<?=$_POST['texture']?>/miniatures/<?=$f?>') no-repeat scroll center right transparent">
				<?=$nom_sous_texture?>
			</option><?php
		}
	}
}
elseif (isset($_POST['partager_bibliotheque'])) {
    Affichage::partager_page();
}

?>
