<?php
include_once 'locales/lang.php';
require_once 'Database.class.php';
require_once 'Inducks.class.php';
Util::exit_if_not_logged_in();

class Stats {
    public static $id_user;

    static function stringToColor($str) {
        return '#'.substr(dechex(crc32($str)), 0, 6);
    }

    static function getPublicationData() {
        $counts= [];
        $resultat_cpt_numeros_groupes=DM_Core::$d->requete(
            'SELECT Pays,Magazine,Count(Numero) AS cpt
             FROM numeros
             WHERE ID_Utilisateur=?
             GROUP BY Pays,Magazine
             ORDER BY cpt desc'
        , [static::$id_user]);

        $publication_codes= [];
        foreach($resultat_cpt_numeros_groupes as $resultat) {
            $publicationcode=$resultat['Pays'].'/'.$resultat['Magazine'];
            $cpt= (int)$resultat['cpt'];
            $counts[$publicationcode]=$cpt;
            $publication_codes[]=$publicationcode;
        }
        $noms_magazines = Inducks::get_noms_complets_magazines($publication_codes);

        $autres=0;
        $nb_magazines_autres=0;
        $data = [];
        $labels = [];
        $colors = [];
        $total = array_sum($counts);
        foreach($counts as $publicationcode=>$cpt) {
            if (array_key_exists($publicationcode, $noms_magazines)) {
                $nom_complet_magazine=$noms_magazines[$publicationcode];
                if ($cpt/$total<0.01) {
                    $autres+=$cpt;
                    $nb_magazines_autres++;
                }
                else {
                    $data[]=$cpt;
                    $labels[]=$nom_complet_magazine;
                    $colors[]= self::stringToColor($publicationcode);
                }
            }
        }
        if ($autres > 0) {
            $data[]=$autres;
            $labels[]=AUTRES.' ('.$nb_magazines_autres.' '.strtolower(PUBLICATIONS).')';
            $colors[]= '#000';
        }
        return ['values' => $data, 'colors' => $colors, 'labels' => $labels];
    }

    static function getConditionData() {
        $resultats=DM_Core::$d->requete('
            SELECT Etat, Count(Numero) AS cpt
            FROM numeros
            WHERE ID_Utilisateur=?
            GROUP BY Etat DESC
            HAVING COUNT(Numero) > 0',
        [static::$id_user]);

        $data = [];
        $labels = [];
        $colors = [];

        foreach($resultats as $resultat) {
            if (array_key_exists($resultat['Etat'], Database::$etats)) {
                $data[]=$resultat['cpt'];
                list($labels[], $colors[])=Database::$etats[$resultat['Etat']];
            }
        }
        return ['values' => $data, 'colors' => $colors, 'labels' => $labels];
    }

    static function getPossessionsData() {
        $resultats_numeros_utilisateur = DM_Core::$d->requete('
            SELECT CONCAT(Pays, \'/\', Magazine) AS publicationcode, COUNT(Numero) AS cpt
            FROM numeros
            WHERE ID_Utilisateur=?
            GROUP BY publicationcode',
        [static::$id_user]);

        $publicationCodes = array_map(function($publicationData) {
            return $publicationData['publicationcode'];
        }, $resultats_numeros_utilisateur);

        $nb_numeros_references=Inducks::get_nb_numeros_magazines($publicationCodes);

        $noms_complets_pays = Inducks::get_noms_complets_pays($publicationCodes);
        $noms_complets_magazines = Inducks::get_noms_complets_magazines($publicationCodes);

        $publicationsData = array_map(function($magazine_et_cpt) use ($nb_numeros_references) {
            $publicationCode = $magazine_et_cpt['publicationcode'];
            $possedesPublication = $magazine_et_cpt['cpt'];
            if (array_key_exists($publicationCode, $nb_numeros_references)) {
                $totalPublication = $nb_numeros_references[$publicationCode];
                $possedesPublicationPct = round(100*($possedesPublication/$totalPublication));
            }
            else {
                $totalPublication = 0;
                $possedesPublicationPct = 0;
            }

            return ((object) [
                'publication_code' => $publicationCode,
                'total' => $totalPublication,
                'possede' => $possedesPublication,
                'possede_pct' => $possedesPublicationPct
            ]);
        }, $resultats_numeros_utilisateur);

        $possedes = [
            'label' => NUMEROS_POSSEDES,
            'backgroundColor' => '#FF8000',
            'data' => array_map(function($publicationData) { return $publicationData->possede; }, $publicationsData)
        ];
        $possedes_cpt = array_merge($possedes, [
            'data' => array_map(function($publicationData) { return $publicationData->possede_pct; }, $publicationsData)
        ]);

        $totaux = [
            'label' => NUMEROS_REFERENCES,
            'backgroundColor' => '#04B404',
            'data' => array_map(function($publicationData) { return $publicationData->total === 0 ? 0 : $publicationData->total - $publicationData->possede; }, $publicationsData)
        ];
        $totaux_cpt = array_merge($totaux, [
            'data' => array_map(function($publicationData) { return 100 - $publicationData->possede_pct; }, $publicationsData)
        ]);

        return [
            'title' => POSSESSION_NUMEROS,
            'legend' => [NUMEROS_POSSEDES, NUMEROS_REFERENCES],
            'datasets' => [
                'possedes' => $possedes, 'totaux' => $totaux,
                'possedes_cpt' => $possedes_cpt, 'totaux_cpt' => $totaux_cpt
            ],
            'labels' => array_map(function($publicationData) { return $publicationData->publication_code; }, $publicationsData),
            'labels_magazines_longs' => $noms_complets_magazines,
            'labels_pays_longs' => $noms_complets_pays,
        ];
    }

    static function getPurchaseHistory() {
            $resultat_achats = DM_Core::$d->requete('
            SELECT IFNULL(DATE_FORMAT(Date,\'%Y-%m\'), \'?\') AS Mois, CONCAT(Pays, \'/\', Magazine) AS Publicationcode, Count(Numero) AS cpt
            FROM numeros n
            LEFT JOIN achats USING (ID_Acquisition)
            WHERE ID_Utilisateur=?
            GROUP BY YEAR(Date), MONTH(Date), Pays,Magazine
            ORDER BY YEAR(Date), MONTH(Date)
        ', [static::$id_user]);

        $achats_magazines_nouv = [];
        $dates = [];

        foreach($resultat_achats as $i=>$achat) {
            $dates[] = $achat['Mois'];
            if (!array_key_exists($achat['Publicationcode'], $achats_magazines_nouv)) {
                $achats_magazines_nouv[$achat['Publicationcode']] = [];
            }
            $achats_magazines_nouv[$achat['Publicationcode']][$achat['Mois']] = (int) $achat['cpt'];
        }

        $publication_codes = array_map(function($achat) {
            return $achat['Publicationcode'];
        }, $resultat_achats);

        $noms_complets_pays = Inducks::get_noms_complets_pays($publication_codes);
        $noms_complets_magazines = Inducks::get_noms_complets_magazines($publication_codes);
        return [
            'dates' => array_values(array_unique($dates)),
            'labels_pays_longs' => $noms_complets_pays,
            'labels_magazines_longs' => $noms_complets_magazines,
            'datasets' => [
              'nouv' => $achats_magazines_nouv,
            ],
            'title' => ACHATS,
            'nouv_hors_date_l10n' => ACHATS_NUMEROS_SANS_DATE,
            'nouv_date_l10n' => ACHATS_NUMEROS_NOUVELLES_ACQUISITIONS,
            'total_date_l10n' => ACHATS_NUMEROS_TAILLE,
            'tous_magazines_l10n' => TOUS_MAGAZINES
        ];
    }

    static function getAuthorStoriesData() {
        $title = POSSESSION_HISTOIRES_AUTEURS;
        $legend = [HISTOIRES_POSSEDEES, HISTOIRES_NON_POSSEDEES];

        $resultat_stats_auteurs = DmClient::get_service_results_for_dm('GET', '/collection/stats/watchedauthorsstorycount');

        $possedees = [
            'label' => HISTOIRES_POSSEDEES,
            'backgroundColor' => '#FF8000',
            'data' => []
        ];
        $possedees_pct = $possedees;

        $manquantes = [
            'label' => HISTOIRES_NON_POSSEDEES,
            'backgroundColor' => '#04B404',
            'data' => []
        ];
        $manquantes_pct = $manquantes;

        $labels = [];

        foreach($resultat_stats_auteurs as $stat_utilisateur_auteur) {
            $auteur = $stat_utilisateur_auteur->fullname;
            $total_auteur = $stat_utilisateur_auteur->storycount;
            $possedees_auteur = $total_auteur - (int)$stat_utilisateur_auteur->missingstorycount;
            $possedees_auteur_pct = round(100*($possedees_auteur/$total_auteur));

            $possedees['data'][]  = $possedees_auteur;
            $manquantes['data'][] = $total_auteur - $possedees_auteur ;

            $possedees_pct['data'][] = $possedees_auteur_pct;
            $manquantes_pct['data'][] = 100 - $possedees_auteur_pct;

            $labels[]=$auteur;
        }


        return [
            'datasets' => [
                'possedees' => $possedees, 'manquantes' => $manquantes,
                'possedees_pct' => $possedees_pct, 'manquantes_pct' => $manquantes_pct
            ],
            'legend' => $legend,
            'labels' => $labels,
            'title' => $title,
        ];
    }

    static function showSuggestedPublications($pays, $sinceLastVisit = false) {
        $statsHasInit = count(DM_Core::$d->requete(
            'SELECT 1 FROM auteurs_pseudos WHERE ID_user=?',
        [$_SESSION['id_user']],
        'db_dm_stats'
        )) > 0;

        if ($statsHasInit) {
            $suggestions = DmClient::get_service_results_for_dm('GET', '/collection/stats/suggestedissues',
                array_merge(
                    is_null($pays) ? ['ALL'] : [$pays],
                    $sinceLastVisit ? ['since_previous_visit'] : ['_'],
                )
            );

            if (!isset($suggestions->issues) || (($nbSuggestions = count(get_object_vars($suggestions->issues))) === 0)) {
                if (!$sinceLastVisit) {
                    ?><br /><?=AUCUNE_SUGGESTION?><?php
                }
            }
            else {
                $minScore = $suggestions->minScore;
                $maxScore = $suggestions->maxScore;

                ob_start();

                foreach($suggestions->issues as $issuecode => $issue) {
                    $publicationcode = $issue->publicationcode;
                    $country = explode('/', $publicationcode)[0];
                    $issuenumber = $issue->issuenumber;
                    $importance = $issue->score === $maxScore ? 1 : ($issue->score === $minScore ? 3 : 2); ?>
                    <div>
                        <span class="numero top<?=$importance?>"><?php
                        Affichage::afficher_texte_numero(
                            $country,
                            $suggestions->publicationTitles->$publicationcode,
                            $issuenumber
                        );
                        ?>&nbsp;</span><?=NUMERO_CONTIENT?>
                    </div>
                    <ul class="liste_histoires"><?php
                    foreach($issue->stories as $author => $storiesOfAuthor) {
                        foreach($storiesOfAuthor as $storyCode) {?>
                            <li>
                                <div class="badge"><?=$suggestions->authors->$author?></div>&nbsp;<?php
                                Affichage::afficher_texte_histoire($storyCode, @$suggestions->storyDetails->$storyCode->title, @$suggestions->storyDetails->$storyCode->storycomment); ?>
                            </li><?php
                        }
                    } ?>
                    </ul><?php
                }

                $suggestionsText = ob_get_clean();

                if ($sinceLastVisit) {
                    Affichage::accordeon(
                        'suggestions-accordion',
                        sprintf($nbSuggestions === 1 ? SUGGESTIONS_ACHATS_NOUVEAUTE : SUGGESTIONS_ACHATS_NOUVEAUTES, $nbSuggestions),
                        '<div id="suggestions" class="small-titles">'.$suggestionsText.'</div>',
                        '<div><a href="/?action=agrandir">See all the suggestions for my collection</a></div>'
                    );
                }
                else {?>
                    <div id="suggestions"><?=$suggestionsText?></div><?php
                }
            }
        }
        else {
            ?><br /><div class="alert alert-info"><?=CALCULS_PAS_ENCORE_FAITS?></div><?php
        }
    }
}

if (isset($_POST['graph'])) {
  Stats::$id_user=$_SESSION['id_user'];

    header('Content-Type: application/json');

    if (isset($_POST['publications'])) {
        echo json_encode(Stats::getPublicationData());
    }
    else if (isset($_POST['conditions'])) {
        echo json_encode(Stats::getConditionData());
    }
    else if (isset($_POST['achats'])) {
        echo json_encode(Stats::getPurchaseHistory());
    }
    else if (isset($_POST['auteurs'])) {
        echo json_encode(Stats::getAuthorStoriesData());
    }
    else if (isset($_POST['possessions'])) {
        echo json_encode(Stats::getPossessionsData());
    }
}
