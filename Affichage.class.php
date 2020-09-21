<?php
include_once 'locales/lang.php';
include_once 'Edge.class.php';
include_once 'Twig.class.php';

class Affichage {

    static $niveaux_medailles=[
        'Photographe' => [1 => 50, 2 => 150, 3 => 600],
        'Createur'  => [1 => 20, 2 => 70,  3 => 150],
        'Duckhunter'  => [1 => 1, 2 => 3,  3 =>  5]
    ];

    static function onglets_magazines(array $onglets_pays,array $onglets_magazines) {
        $magazine_courant = $_GET['onglet_magazine'] ?? null;
        Twig::$twig->display('publication_tabs.twig', [
            'country_names' => $onglets_pays,
            'publication_names' => $onglets_magazines,
            'current_publicationcode' => $magazine_courant,
            'current_countrycode' => is_null($magazine_courant) ? null : explode('/', $magazine_courant)[0],
        ]);
    }

    static function onglets($onglet_courant, $tab_onglets, $argument, $prefixe) {
        Twig::$twig->display('tabs.twig', [
            'current_tab' => $onglet_courant,
            'tabs' => $tab_onglets,
            'argument' => $argument,
            'prefix' => $prefixe,
        ]);
    }

    /**
     * @param Liste $liste
     * @param string $pays
     * @param string $magazine
     */
    static function afficher_numeros($liste, $pays, $magazine) {
        date_default_timezone_set('Europe/Paris');
        [$numeros,$sous_titres] =Inducks::get_numeros($pays,$magazine);

        if ($numeros==false) {
            echo AUCUN_NUMERO_REPERTORIE.$magazine.' ('.PAYS_PUBLICATION.' : '.$pays.')';
            ?><br /><br /><?php
            echo QUESTION_SUPPRIMER_MAGAZINE;
            $liste->sous_liste($pays,$magazine)->afficher('Classique');
            ?><br />
            <a href="?action=gerer&supprimer_magazine=<?=$pays.'.'.$magazine?>"><?=OUI?></a>&nbsp;
            <a href="?action=gerer"><?=NON?></a><?php
            if (!Util::isLocalHost()) {
                @mail('admin@ducksmanager.net', 'Erreur de recuperation de numeros', AUCUN_NUMERO_REPERTORIE . $magazine . ' (' . PAYS_PUBLICATION . ' : ' . $pays . ')');
            }
        }
        else {
            $liste->nettoyer_collection();
            $nb_possedes = 0;
            $numeros = array_map(function ($numero, $sous_titre) use ($liste, $pays, $magazine, &$nb_possedes) {
                $infos_numero = $liste->get_numero_collection($pays, $magazine, $numero);
                $o = new stdClass();
                $o->est_possede = false;
                if (!is_null($infos_numero)) {
                    $nb_possedes++;
                    $o->est_possede = true;
                    [/*Pays*/, /*Magazine*/, /*Numero*/, $o->etat, $o->av, $o->id_acquisition, $o->date_acquisition, $o->description_acquisition] = $infos_numero;
                    $o->etat = array_key_exists($o->etat, Database::$etats) ? $o->etat : 'indefini';
                }
                $o->sous_titre = $sous_titre;
                $o->numero = $numero;
                return $o;
            }, $numeros, $sous_titres);
            Twig::$twig->display('issue_list.twig', [
                'country' => $pays,
                'publicationcode' => $magazine,
                'publicationname' => Inducks::get_nom_complet_magazine($pays, $magazine),
                'possessed_number' => $nb_possedes,
                'non_possessed_number' => count($numeros) - $nb_possedes,
                'issues' => $numeros,
                'locale' => $_SESSION['lang']
            ]);
        }
    }

    static function afficher_evenements_recents($evenements) {
        if (count($evenements->evenements) > 0) {
            include_once 'Edge.class.php';

            $magazines_complets=Inducks::get_noms_complets_magazines($evenements->publicationcodes);
            $details_collections=DM_Core::$d->get_details_collections($evenements->ids_utilisateurs);

            foreach($evenements->evenements as $evenements_date) {
                foreach($evenements_date as $type=>$evenements_type) {
                    foreach($evenements_type as $evenement) {
                        ?><div class="evenement evenement_<?=$type?>"><?php
                        switch($type) {
                            case 'inscriptions':
                                self::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);
                                ?><?=NEWS_A_COMMENCE_COLLECTION?>
                            <?php
                            break;
                            case 'medaille':
                                switch($evenement->contribution) {
                                    case 'photographe': $titre_medaille = TITRE_MEDAILLE_PHOTOGRAPHE; break;
                                    case 'createur': $titre_medaille = TITRE_MEDAILLE_CREATEUR; break;
                                    case 'duckhunter': $titre_medaille = TITRE_MEDAILLE_DUCKHUNTER; break;
                                    default: break 2;
                                }
                                self::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);
                                ?><?=sprintf(NEWS_A_OBTENU_MEDAILLE, $titre_medaille, $evenement->niveau)?>
                            <?php
                            break;
                            case 'bouquineries':
                                self::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);?>
                                <?=NEWS_A_AJOUTE_BOUQUINERIE.' ' ?>
                                <i><a href="?action=bouquineries"><?=$evenement->nom_bouquinerie?></a></i>.
                            <?php
                            break;
                            case 'ajouts':
                                $numero=$evenement->numero_exemple;
                                if (!array_key_exists($numero->Pays.'/'.$numero->Magazine, $magazines_complets)) {
                                    $evenement->cpt++;
                                    continue 2;
                                }
                                self::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);
                                ?><?=NEWS_A_AJOUTE?>
                                <?php self::afficher_texte_numero($numero->Pays, $magazines_complets[$numero->Pays . '/' . $numero->Magazine], $numero->Numero); ?>
                                <?php
                                if ($evenement->cpt > 0) {
                                    ?>
                                    <?=ET?> <?=$evenement->cpt?>
                                    <?=$evenement->cpt === 1 ? NEWS_AUTRE_NUMERO : NEWS_AUTRES_NUMEROS?>
                                <?php } ?>
                                <?=NEWS_A_SA_COLLECTION?><?php
                            break;
                            case 'tranches_pretes':
                                $numero=$evenement->numeros[0];
                                if (!array_key_exists($numero->Pays.'/'.$numero->Magazine, $magazines_complets)) {
                                    $evenement->cpt++;
                                    continue 2;
                                }
                                $contributeurs = array_filter(array_unique($evenement->ids_utilisateurs));
                                foreach($contributeurs as $i => $idContributeur) {
                                    self::afficher_texte_utilisateur($details_collections[$idContributeur]);
                                    ?><?= $i < count($contributeurs) -2 ? ', ' : ($i < count($contributeurs) - 1 ? ' ' . ET . ' ' : '');
                                }

                                ?><?=count($contributeurs) === 1 ? NEWS_A_CREE_TRANCHE : NEWS_ONT_CREE_TRANCHE?>
                                <a href="javascript:void(0)" class="has_tooltip edge_tooltip underlined">
                                    <?php
                                    self::afficher_texte_numero(
                                            $numero->Pays,
                                            $magazines_complets[$numero->Pays.'/'.$numero->Magazine],
                                            $numero->Numero,
                                            count($evenement->numeros) - 1,
                                            false
                                    );?>
                                </a>
                                <span class="cache tooltip_content">
                                    <div class="edge_container">
                                        <?php
                                        foreach($evenement->numeros as $numero) {
                                            $e=new Edge($numero->Pays, $numero->Magazine, $numero->Numero, $numero->Numero, true);
                                            echo $e->getImgHTML(true);
                                        }
                                    ?></div><?php
                                    foreach($evenement->numeros as $numero) {
                                        self::afficher_texte_numero(
                                            $numero->Pays, $magazines_complets[$numero->Pays . '/' . $numero->Magazine], $numero->Numero
                                        );
                                        ?><br /><?php
                                    }
                                    ?>
                                </span>
                                <?=NEWS_ONT_CREE_TRANCHE_2?>
                                <?php
                            break;
                        }
                        self::afficher_temps_passe((int)$evenement->diffsecondes);
                        ?></div><?php
                    }
                }
            }
        }
    }

    static function afficher_dernieres_tranches_publiees() {
        $id_user= empty($_SESSION['id_user']) ? null : $_SESSION['id_user'];

        $resultat_tranches_collection_ajoutees = DM_Core::$d->get_tranches_collection_ajoutees($id_user, true);
        $nb_nouvelles_tranches = count($resultat_tranches_collection_ajoutees);

        if ($nb_nouvelles_tranches > 0) {
            $magazines_complets = Inducks::get_noms_complets_magazines(array_map(function($tranche) { return $tranche['publicationcode']; }, $resultat_tranches_collection_ajoutees));
            $liste_numeros = array_map(function($tranche) use ($magazines_complets) {
                return '<li>'.Twig::$twig->render('issue.twig', [
                    'country' => explode('/', $tranche['publicationcode'])[0],
                    'magazine' => $magazines_complets[$tranche['publicationcode']],
                    'issuenumber' => $tranche['issuenumber']
                ]).'</li>';
            }, $resultat_tranches_collection_ajoutees);
            self::accordeon(
                'nouvelles-tranches',
                sprintf($nb_nouvelles_tranches === 1 ? BIBLIOTHEQUE_NOUVELLE_TRANCHE_TITRE : BIBLIOTHEQUE_NOUVELLES_TRANCHES_TITRE, $nb_nouvelles_tranches),
                '<ul class="liste_histoires no-indent">'.implode('', $liste_numeros).'</ul>',
                $nb_nouvelles_tranches === 1 ? BIBLIOTHEQUE_NOUVELLE_TRANCHE_CONTENU : BIBLIOTHEQUE_NOUVELLES_TRANCHES_CONTENU
            );
        }
    }

    static function accordeon($id, $title, $content, $footer = null, $icon = 'glyphicon-info-sign', $collapsed = true) {
        Twig::$twig->display('accordion.twig', compact('id', 'collapsed', 'icon', 'title', 'content', 'footer'));
    }

    static function afficher_temps_passe($diff_seconds) {
        Twig::$twig->display('ago.twig', compact('diff_seconds'));
    }

    static function afficher_texte_numero($country, $magazine, $issuenumber, $other_issues_number = 0, $allow_wrap = true) {
        Twig::$twig->display('issue.twig', compact('country', 'magazine', 'issuenumber', 'other_issues_number', 'allow_wrap'));
    }

    static function afficher_texte_numero_template() {
        ?><div class="template issue_title">
            <span class="nowrap">
                <img class="flag" />&nbsp;
            </span>
            <span class="publication_name"></span> <span class="issuenumber"></span>
        </div><?php
    }

    static function afficher_infobulle_tranche_template() {
        ?><div class="template tooltip_edge_content">
            <?=DECOUVRIR_COUVERTURE?>.
            <div class="has-no-edge">
                <?=TRANCHE_NON_DISPONIBLE1?><br />
                <div class="is-not-bookcase-share">
                    <?=TRANCHE_NON_DISPONIBLE2?><br />
                    <div class="template progress-wrapper">
                        <img class="possede-medaille medaille_objectif gauche" />
                        <img class="possede-medaille-non-max medaille_objectif droite" />
                        <div class="progress">
                            <div class="progress-current progress-bar progress-bar-muted" role="progressbar"></div>
                            <div class="progress-extra progress-bar progress-bar-success active nowrap show_overflow progress-bar-striped" role="progressbar">
                            </div>

                        </div>
                    </div>
                    <div class="progress-info">
                        <?= TRANCHE_NON_DISPONIBLE3 ?>
                        <span class="progress-extra-points"></span> <?=POINTS?> !
                    </div>
                    <br />
                    <a href="https://edgecreator.ducksmanager.net" target="_blank" class="btn btn-info">
                        <?= ENVOYER_PHOTO_DE_TRANCHE ?>
                    </a>
                </div>
            </div>
        </div><?php
    }

    static function afficher_proposition_photo_tranche() {
        ?><?=sprintf(INVITATION_ENVOI_PHOTOS_TRANCHES, '<span class="max-points-to-earn"></span>')?>
        <div class="carousel small slide">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <li class="indicator template"></li>
            </ol>
            <img class="possede-medaille medaille_objectif gauche" />
            <div class="carousel-inner">
                <div class="item template">
                </div>
            </div>
            <img class="possede-medaille-non-max medaille_objectif droite" />

            <!-- Left and right controls -->
            <a class="left carousel-control" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
                <span class="sr-only">Next</span>
            </a>
            <div class="wrapper_envoyer_tranches">
                <a href="https://edgecreator.ducksmanager.net" target="_blank" class="btn btn-info">
                    <?=ENVOYER_PHOTOS_DE_TRANCHE?></a>
            </div>
        </div>
        <?php
    }

    static function afficher_texte_utilisateur($userData) {
        $medals = self::get_medailles([
            'Photographe'=> $userData['Points']['Photographe'],
            'Createur' => $userData['Points']['Createur'],
            'Duckhunter' => $userData['Points']['Duckhunter']
        ]);
        Twig::$twig->display('user.twig', ['DOMAIN' => Util::DOMAIN, 'user_data' => $userData, 'medals' => $medals]);
    }

    static function afficher_texte_histoire($code, $title, $comment) {
        Twig::$twig->display('story.twig', compact('code', 'title', 'comment'));
    }

    static function valider_formulaire_inscription($user, $pass, $pass2) {
        $erreur=null;
        if (isset($user)) {
            if (preg_match('#^[-_A-Za-z0-9]{3,15}$#', $user) === 0) {
                return UTILISATEUR_INVALIDE;
            }
            if (strlen($pass) <6) {
                return MOT_DE_PASSE_6_CHAR_ERREUR;
            }
            if ($pass !== $pass2) {
                return MOTS_DE_PASSE_DIFFERENTS;
            }
            if (DM_Core::$d->user_exists($user)) {
                return UTILISATEUR_EXISTANT;
            }
        }
        else {
            return UTILISATEUR_INVALIDE;
        }
        return null;
    }

    static function partager_page() {
        ?><div class="a2a_kit a2a_kit_size_32 a2a_default_style"
               data-a2a-url="<?=Edge::get_lien_bibliotheque($_SESSION['user'])?>"
               data-a2a-title="Ma bibliothèque DucksManager">
            <a class="noborder a2a_button_email"></a>
            <a class="noborder a2a_button_facebook"></a>
            <a class="noborder a2a_button_twitter"></a>
            <a class="noborder a2a_button_google_plus"></a>
        </div><?php
    }

    public static function afficher_stats_collection_court($nb_pays, $nb_magazines, $nb_numeros) {
        echo sprintf(
            '%s %s.<br />%s %s %s %s %s.',
            $nb_numeros,
            NUMEROS,
            POSSESSION_MAGAZINES_2,
            $nb_magazines,
            POSSESSION_MAGAZINES_3,
            $nb_pays,
            PAYS
        );
    }

    public static function get_medailles($points)
    {
        $points_et_niveaux = [];
        foreach ($points as $contribution => $points_contribution) {
            $points_et_niveaux[$contribution] = ['Cpt' => $points_contribution, 'Niveau' => 0];
            foreach (self::$niveaux_medailles[$contribution] as $niveau => $points_min) {
                if ($points_contribution >= $points_min) {
                    $points_et_niveaux[$contribution]['Niveau'] = $niveau;
                }
            }
        }
        return $points_et_niveaux;
    }

    public static function afficher_statut_connexion($est_connecte) {
        ?><div id="login">
            <a class="logo_petit" href="<?= isset($_SESSION['user']) ? '/?action=gerer' : '/' ?>"><img src="/logo_nom.jpg" /></a>
            <div id="texte_connecte"><?php
                if ($est_connecte) {?>
                    <img id="light" src="vert.png" alt="O" />&nbsp;
                    <span><?=$_SESSION['user']?></span><?php
                }
            ?>
            </div>
        </div><?php
    }
}
?>
