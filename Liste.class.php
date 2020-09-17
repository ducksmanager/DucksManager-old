<?php
require_once 'DucksManager_Core.class.php';
require_once 'Affichage.class.php';
require_once 'Inducks.class.php';
class Liste {
    var $texte;
    var $collection= [];
    static $types_listes= [];

    static function set_types_listes() {
        $rep = "Listes/";
        $dir = opendir($rep);
        $prefixe='Liste.';
        $suffixe='.class.php';
        while ($f = readdir($dir)) {
            if (strpos($f,'Debug')!==false) {
                continue;
            }
            if (is_file($rep . $f) && startswith($f, $prefixe) && endswith($f, $suffixe)) {
                $nom=substr($f,strlen($prefixe),strlen($f)-strlen($suffixe)-strlen($prefixe));

                include_once('Listes/Liste.'.$nom.'.class.php');
                $a=new ReflectionProperty($nom, 'titre');
                self::$types_listes[$nom]=$a->getValue();
            }
        }
        return self::$types_listes;
    }

    function __construct($texte=false) {
        if (!$texte) {
            return;
        }
        $this->texte=$texte;
    }

    function sous_liste($pays,$magazine=false) {
        $nouvelle_liste=new Liste();
        if (!$magazine) {
            $nouvelle_liste->collection= [$pays=> [$this->collection[$pays]]];
        }
        else {
            if (isset($this->collection[$pays][$magazine])) {
                $nouvelle_liste->collection= [$pays=> [$magazine=>$this->collection[$pays][$magazine]]];
            }
        }
        return $nouvelle_liste;
    }

    function get_publication_la_plus_possedee() {
        $cpt_numeros = [];
        foreach($this->collection as $pays=>$numeros_pays) {
            foreach(array_keys($numeros_pays) as $magazine) {
                $cpt_numeros[$pays.'/'.$magazine] = count($numeros_pays[$magazine]);
            }
        }
        if (count($cpt_numeros) > 0) {
            arsort($cpt_numeros);
            return key($cpt_numeros);
        }
        return null;
    }

    function liste_magazines($pays_magazine_supplementaire=null) {
        $publication_codes= [];
        foreach($this->collection as $pays=>$numeros_pays) {
            foreach(array_keys($numeros_pays) as $magazine) {
                $publication_codes[]=$pays.'/'.$magazine;
            }
        }
        if (!is_null($pays_magazine_supplementaire)) {
            $publication_codes[]=$pays_magazine_supplementaire;
        }
        $noms_pays = Inducks::get_noms_complets_pays($publication_codes);
        $noms_magazines = Inducks::get_noms_complets_magazines($publication_codes);
        return [$noms_pays,$noms_magazines];
    }

    function statistiques($onglet) {
        $id_user=$_SESSION['id_user'];

        $counts= [];
        foreach($this->collection as $pays=>$numeros_pays) {
            $counts[$pays]= [];
            foreach($numeros_pays as $magazine=>$numeros) {
                $counts[$pays][$magazine]=count($numeros);
            }
        }
        $onglets= [PUBLICATIONS=> ['magazines',PUBLICATIONS_COURT],
                               POSSESSIONS=> ['possessions',POSSESSIONS_COURT],
                               ETATS_NUMEROS=> ['etats',ETATS_NUMEROS_COURT],
                               ACHATS=> ['achats',ACHATS_COURT],
                               AUTEURS=> ['auteurs',AUTEURS_COURT]];
        Affichage::onglets($onglet,$onglets,'onglet','?action=stats');

        if (count($counts)===0) {
            ?><div class="alert alert-info">
                <?=AUCUN_NUMERO_POSSEDE_1?>
                <a href="?action=gerer&onglet=ajout_suppr"><?=ICI?></a>
                <?=AUCUN_NUMERO_POSSEDE_2?></div>
            <?php
            return;
        }
        switch($onglet) {
            case 'magazines':
                ?>
                <div id="canvas-holder">
                    <canvas id="graph_publications"></canvas>
                </div><?php
            break;
            case 'possessions':
                $types = ['abs' => AFFICHER_VALEURS_REELLES, 'cpt'=> AFFICHER_POURCENTAGES]; ?>

                <div id="canvas-controls" class="btn-group hidden" data-toggle="buttons">
                    <?php foreach($types as $type=>$label) {?>
                        <label class="btn btn-default graph_type <?=$type==='abs' ? 'active': ''?>" onclick="toggleGraphs(this, 'possessions')">
                            <input type="radio" name="options_graph" autocomplete="off" /> <?=$label?>
                        </label><?php
                    }?>
                </div>
                <br />
                <div id="message_possessions"><?=CHARGEMENT?></div>
                <div id="canvas-holder" class="hidden">
                    <?php foreach($types as $type=>$label) {
                        ?><canvas class="graph_possessions <?=$type?> <?=$type==='cpt' ? 'hidden' : ''?>"
                                  width="100%" height="500px"></canvas><?php
                    }?>
                </div><?php
                break;
            case 'etats': ?>
                <div id="canvas-holder">
                    <canvas id="graph_conditions"></canvas>
                </div><?php
            break;

            case 'achats':
                $types = ['nouv' => AFFICHER_NOUVELLES_ACQUISITIONS, 'tot'=> AFFICHER_POSSESSIONS_TOTALES]; ?>

                <div id="message_achats"><?=CHARGEMENT?></div>
                <div class="alert alert-info">
                    <div><?=EXPLICATION_GRAPH_ACHATS_1?></div><br />
                    <div><?=EXPLICATION_GRAPH_ACHATS_2?></div>
                    <div><?=EXPLICATION_GRAPH_ACHATS_3?></div>
                    <div><?=sprintf(EXPLICATION_GRAPH_ACHATS_4, '<a href="/?action=gerer">'.GERER_COLLECTION.'</a>')?></div>
                    <div id="message_achats_vide" class="hidden">
                        <a href="/?action=gerer">
                            <img height="300px" src="images/demo_selection_achat_<?=$_SESSION['lang']?>.png" />
                        </a>
                    </div>
                </div>
                <br />

                <div id="fin_achats" class="hidden">
                    <div class="btn-group" data-toggle="buttons">
                        <?php foreach($types as $type=>$label) {?>
                        <label class="btn btn-default graph_type <?=$type==='abs' ? 'active': ''?>" onclick="toggleGraphs(this, 'achats')">
                            <input type="radio" name="options_graph" autocomplete="off" /> <?=$label?>
                            </label><?php
                        }?>
                    </div>
                    <div id="canvas-holder" class="hidden" style="background: whitesmoke">
                        <?php foreach($types as $type=>$label) {
                            ?><canvas class="graph_achats <?=$type?> <?=$type==='tot' ? 'hidden' : ''?>"
                                      width="100%" height="500px"></canvas><?php
                        }?>
                    </div>
                </div>
                <?php
            break;
            case 'auteurs':
                $requete_auteurs_surveilles='SELECT NomAuteurAbrege FROM auteurs_pseudos WHERE ID_User='.$id_user;
                $resultats_auteurs_surveilles=DM_Core::$d->requete($requete_auteurs_surveilles);
                if (count($resultats_auteurs_surveilles) === 0) { ?>
                    <div class="alert alert-warning"><?=AUCUN_AUTEUR_SURVEILLE?></div><?php
                }
                else {
                    $types = ['abs' => AFFICHER_VALEURS_REELLES, 'pct'=> AFFICHER_POURCENTAGES]; ?>

                    <div id="aucun_resultat_stats_auteur" class="alert alert-info hidden">
                        <?=CALCULS_PAS_ENCORE_FAITS?>
                    </div>
                    <div id="chargement_stats_auteur">
                        <?=CHARGEMENT?>
                    </div>
                    <div id="fin_stats_auteur" class="hidden">
                        <div class="btn-group" data-toggle="buttons">
                            <?php foreach($types as $type=>$label) {?>
                            <label class="btn btn-default graph_type <?=$type==='abs' ? 'active': ''?>" onclick="toggleGraphs(this, 'auteurs')">
                                <input type="radio" name="options_graph" autocomplete="off" /> <?=$label?>
                                </label><?php
                            }?>
                        </div>
                    </div>
                    <br />
                    <div id="canvas-holder" class="hidden">
                        <?php foreach($types as $type=>$label) {
                            ?><canvas class="graph_auteurs <?=$type?> <?=$type==='pct' ? 'hidden' : ''?>"></canvas><?php
                        }?>
                    </div>
                    <?php
                }
                ?><br /><br />
                <?=STATISTIQUES_QUOTIDIENNES?>
                <br /><br />
                <hr /><?php
                if (isset($_POST['auteur_id'])) {
                    DM_Core::$d->ajouter_auteur($_POST['auteur_id']);
                }
                ?>
                <br /><br />
                <?=AUTEURS_FAVORIS_INTRO_1?>
                <a href="?action=agrandir&onglet=suggestions_achat"><?=AUTEURS_FAVORIS_INTRO_2?></a>
                <br /><br />
                <div style="clear: both">
                    <br /><br /><?php
                    DM_Core::$d->afficher_liste_auteurs_surveilles($resultats_auteurs_surveilles); ?>
                </div>
                <div class="form-group">
                    <form method="post" class="row" action="?action=stats&amp;onglet=auteurs">
                        <label for="auteur_nom" class="col-sm-1 control-label" style="white-space: nowrap"><?=AUTEUR?> :</label>
                        <div class="col-sm-4">
                            <input class="form-control" autocomplete="off" type="text" name="auteur_nom" id="auteur_nom" value="" />
                        </div>
                        <input type="hidden" id="auteur_id" name="auteur_id" />
                        <div class="col-sm-4">
                            <input class="btn btn-default center" type="submit" value="<?=AJOUTER?>" />
                        </div>
                    </form>
                </div><?php
            break;
        }
    }

    function remove_from_database($id_user) {
        $cpt=0;
        foreach($this->collection as $pays=>$numeros_pays) {
            if ($pays!=='country') {
                foreach($numeros_pays as $magazine=>$numeros) {
                    foreach($numeros as $numero) {
                        $num_final=is_array($numero) && array_key_exists(4,$numero) ? $numero[4] : $numero;
                        $requete='DELETE FROM numeros WHERE (ID_Utilisateur ='.$id_user.' AND PAYS = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$num_final.'\')';
                        DM_Core::$d->requete($requete);
                        $cpt++;
                    }
                }
            }
        }
        return $cpt;
    }

    function afficher($type,$parametres=null) {
        $type=strtolower($type);
        if (@require_once 'Listes/Liste.'.$type.'.class.php') {
            /** @var Liste $o */
            $o=new $type();
            if (!is_null($parametres)) {
                foreach($parametres as $nom_parametre=>$parametre) {
                    $o->parametres->$nom_parametre = $parametre;
                }
            }
            $o->afficher($this->collection);
        }
        else {
            echo ERREUR_TYPE_LISTE_INVALIDE;
        }
    }

    function get_etat_numero_possede($pays, $magazine, $numero) {
        if (array_key_exists($pays, $this->collection)
         && array_key_exists($magazine, $this->collection[$pays])) {
            foreach($this->collection[$pays][$magazine] as $numero_liste) {
                if (nettoyer_numero($numero_liste[2])===$numero) {
                    return $numero_liste[3];
                }
            }
        }
        return null;
    }
    function get_numero_collection($pays, $magazine, $numero) {
        if (isset($this->collection[$pays][$magazine])
         && array_key_exists($numero, $this->collection[$pays][$magazine])) {
            return $this->collection[$pays][$magazine][$numero];
        }
        return null;
    }

    function nettoyer_collection() {
        foreach($this->collection as $pays=>$liste_magazines) {
            foreach($liste_magazines as $magazine=>$liste_numeros) {
                foreach($liste_numeros as $i=>&$numero_liste) {
                    $numero_liste[0] = nettoyer_numero($numero_liste[0]);
                }
                unset($numero_liste);
            }
        }
    }
}
if (isset($_POST['parametres'])) {
    $_POST['parametres'] = str_replace('\"', '"', $_POST['parametres']);
}

function startswith($hay, $needle) {
    return $needle === $hay or strpos($hay, $needle) === 0;
}

function endswith($hay, $needle) {
    return $needle === $hay or strpos(strrev($hay), strrev($needle)) === 0;
}
?>
